<?php
namespace Alph\Services;

use Alph\Services\History;
use Alph\Services\SenderData;
use Alph\Services\Session;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class CommandHandler implements MessageComponentInterface
{
    protected $clients;

    /**
     * @var \PDO
     */
    private $db;
    private $commands;
    public $data;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->db = \Alph\Services\Database::connect();
        $this->commands = \Alph\Services\DefinedCommands::get();

        /**
         * @var SenderData[]
         */
        $this->data = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages later
        $this->clients->attach($conn);

        $this->data[$conn->resourceId] = new SenderData;
    }

    public function onMessage(ConnectionInterface $sender, $cmd)
    {
        // Get cookie HTTP header
        $cookies = $sender->httpRequest->getHeader('Cookie');

        // If there is no values in the cookie header, stop the process
        if (!empty($cookies)) {
            // Parse the cookies to obtain each cookies separately
            $parsed_cookies = \GuzzleHttp\Psr7\parse_header($cookies);

            // Check if alph_sess is defined in the sender's cookies
            if (isset($parsed_cookies[0]["alph_sess"]) && isset($parsed_cookies[0]["terminal"])) {
                // Read the sender's session data
                $sender_session = Session::read($this->db, $parsed_cookies[0]["alph_sess"]);

                // Check if the idaccount is present in the sender's session
                if (!empty($sender_session["account"]["idaccount"])) {
                    if ($this->data[$sender->resourceId]->credentials->connected) {
                        // Parse the command in 2 parts: the command and the parameters, the '@' remove the error if parameters index is null
                        @list($cmd, $parameters) = explode(' ', $cmd, 2);

                        // Check if the command exists
                        if (in_array($cmd, $this->commands)) {
                            // Call the command with arguments
                            \call_user_func_array('\\Alph\\Commands\\' . $cmd . '::call', [$this->db, $this->clients, &$this->data[$sender->resourceId], $sender, $parsed_cookies[0]["alph_sess"], $sender_session, $parsed_cookies[0]["terminal"], $cmd, $parameters]);
                        } else {
                            $sender->send("-bash: " . $cmd . ": command not found");
                        }

                        // Push the command into the history
                        History::push($this->db, 1, $sender_session["account"]["idaccount"], $cmd . ' ' . $parameters);
                    } else {
                        if (!empty($this->data[$sender->resourceId]->credentials->username) && !isset($this->data[$sender->resourceId]->credentials->password)) {
                            $stmp = $this->db->prepare("SELECT password FROM TERMINAL_USER WHERE username = :username AND terminal = :terminal;");

                            $terminal_mac = str_replace(['.', '-'], ':', strtoupper($parsed_cookies[0]["terminal"]));

                            $stmp->bindParam(":username", $this->data[$sender->resourceId]->credentials->username);
                            $stmp->bindParam(":terminal", $terminal_mac);

                            $stmp->execute();

                            $row = $stmp->fetch(\PDO::FETCH_ASSOC);

                            if(\password_verify($cmd, $row["password"])) {
                                $sender->send("Connected!");

                                $this->data[$sender->resourceId]->credentials->connected = true;
                            } else {
                                $sender->send("Access denied.");
                            }
                        } else {
                            $this->data[$sender->resourceId]->credentials->username = $cmd;
                        }
                    }
                } else {
                    $sender->send("alph: account connection error");
                }
            } else {
                $sender->send("alph: terminal connection error");
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        unset($this->data[$conn->resourceId]);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
