<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class touch implements CommandInterface
{
    const USAGE = "touch [OPTION]... FILE...";

    const SHORT_DESCRIPTION = "touch - change file timestamps";

    const FULL_DESCRIPTION = " Update the access and modification times of each FILE to the current time.
    A FILE argument that does not exist is created empty, unless -c or -his supplied.
    A FILE argument string of - is handled specially and causes touch to change the times of the file associated with standard output.
    Mandatory arguments to long options are  mandatory  for  short  options too.";

    const OPTIONS = [
        "-a" => "change only the access time",
        "-c" => "--no-create do not create any files",
        "-d" => "--date=STRING parse STRING and use it instead of current time",
        "-f" => "(ignored)",
        "-h" => "--no-dereference affect each symbolic link instead of any referenced file (useful only on systems that can change the timestamps of a symlink)",
        "-m" => "change only the modification time",
        "-r" => "--reference=FILE use this file's times instead of current time",
        "-t" => "STAMP use [[CC]YY]MMDDhhmm[.ss] instead of current time",
        "--time=WORD" => "change the specified time: WORD is access, atime, or use: equiv‐ alent to -a WORD is modify or mtime: equivalent to -m",
        "--help" => "display this help and exit",
        "--version" => "output version information and exit",
    ];

    const ARGUMENTS = [
        "PATTERN" => "touch: missing file operand.
                      Try 'touch --help' for more information.",
    ];

    const EXIT_STATUS = "";

    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $basicmod = 777;
        $params = "";
        $positionDir = "KO";
        $dataFile = "";

        // If no params
        if (empty($parameters)) {
            $sender->send("<br>Opérande manquant<br>Saisissez touch --help pour plus d'information");
            return;
        } else {

            // if ($data->position == '/') {
            //     $positionDir = "ok";
            // } else {
            //     $position = explode("/", $data->position);
            //     $positionDir = $position[count($position) - 1];
            // }

            // Get parameters
            $paramList = explode(" ", $parameters);

            $name = $paramList[0];

            $dataFile = "OK";

            // Prepare
            $stmp = $db->prepare("INSERT INTO TERMINAL_FILE(terminal, parent, name, data, chmod, owner, `group`, createddate, editeddate) VALUES(:terminal, :parent, :name, :data, :chmod, :owner, (SELECT gid FROM terminal_user WHERE idterminal_user = :owner), NOW(),NOW());");

            // Bind parameters put in SQL
            $stmp->bindParam(":terminal", $terminal_mac);
            $stmp->bindParam(":parent", $positionDir);
            $stmp->bindParam(":name", $name);
            $stmp->bindParam(":data", $dataFile);
            $stmp->bindParam(":chmod", $basicmod, \PDO::PARAM_INT);
            $stmp->bindParam(":owner", $data->user->idterminal_user);

            $stmp->execute();
        }
    }

}
