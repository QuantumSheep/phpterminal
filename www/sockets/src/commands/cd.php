<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class cd implements CommandInterface
{
    const USAGE = "cd [dir]";

    const SHORT_DESCRIPTION = "Change the shell working directory.";
    const FULL_DESCRIPTION = "Change the current directory to DIR.  The default DIR is the value of the HOME shell variable.";

    const EXIT_STATUS = "Returns 0 if the directory is changed.";

    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters, bool &$lineReturn)
    {
        $path = [];

        // cd by himself return to /home
        if (empty($parameters)) {
            return $data->position = '/home';
        }

        $quotedParameters = CommandAsset::getQuotedParameters($parameters, $data->position);

        if (!empty($parameters)) {
            $path = explode(' ', $parameters);
        }

        CommandAsset::concatenateParameters($path, $quotedParameters);

        // Test if multi argument
        if (isset($path[1])) {
            $sender->send("message|<br>Error : Multiple argument");
            return;
        }

        // case parameters is help
        if ($path[0] == '--help') {
            $parameters = 'cd';
            return help::call(...\func_get_args());
        }

        if ($path[0] == '/') {
            return $data->position = "/";
        }

        // Get multiple elements for parameters treatment
        $absolutePath = CommandAsset::getAbsolute($data->position, $path[0]);

        $DirName = explode("/", $absolutePath)[count(explode("/", $absolutePath)) - 1];
        $ParentId = CommandAsset::getParentId($db, $terminal_mac, $absolutePath);

        //check if directory exist
        if ($ParentId != null) {
            //check if directory is accessible
            if (CommandAsset::checkRightsTo($db, $terminal_mac, $data->user->idterminal_user, $data->user->gid, $absolutePath, CommandAsset::getChmod($db, $terminal_mac, $DirName, $ParentId), 1)) {

                $stmp = $db->prepare("SELECT IdDirectoryFromPath(?, ?) as idDirectory;");
                $stmp->execute([$absolutePath, $terminal_mac]);
                if ($stmp->rowCount() === 1) {
                    $row = $stmp->fetch(\PDO::FETCH_ASSOC);
                    if ($row["idDirectory"] !== null) {
                        return $data->position = $absolutePath;
                    }
                }
            } else {

                return $sender->send("message|<br> You don't have rights to access this directory");
            }

        }

        $sender->send("message|<br>Error : " . $path[0] . " directory doesn't exists");
    }
}
