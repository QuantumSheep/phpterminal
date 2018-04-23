<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Ratchet\ConnectionInterface;
use Alph\Services\SenderData;

class history implements CommandInterface
{
    const USAGE = "history [-c] [-d offset] [n] or history -anrw [filename] or history -p arg [arg...]";

    const SHORT_DESCRIPTION = "Display or manipulate the history list.";

    const FULL_DESCRIPTION = "Display the history list with line numbers, prefixing each modified
    entry with a `*'.  An argument of N lists only the last N entries.";

    const OPTIONS = [
        "-c" => "clear the history list by deleting all of the entries",
        "-d" => "offset delete the history entry at position OFFSET.",

        "-a" => "append history lines from this session to the history file",
        "-n" => "read all history lines not already read from the history file
                 and append them to the history list",
        "-r" => "read the history file and append the contents to the history
                 list",
        "-w" => "write the current history to the history file",

        "-p" => "perform history expansion on each ARG and display the result
                 without storing it in the history list",
        "-s" => "append the ARGs to the history list as a single entry",
    ];

    const ARGUMENTS = [
        "PATTERN" => "    If FILENAME is given, it is used as the history file.  Otherwise,
        if HISTFILE has a value, that is used, else ~/.bash_history.

        If the HISTTIMEFORMAT variable is set and not null, its value is used
        as a format string for strftime(3) to print the time stamp associated
        with each displayed history entry.  No time stamps are printed otherwise.",
    ];

    const EXIT_STATUS = "Returns success unless an invalid option is given or an error occurs.";

    /**
     * Call the command
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $check = $db->prepare("SELECT command FROM terminal_user_history WHERE terminal_user = :terminal_user");
        $check->bindParam(":terminal_user", $data->user->idterminal_user);
        $check->execute();

        $history = $check->fetchAll();
        $Counter = count($history);

        if ($parameters != null) {
            $params_parts = explode(' ', $parameters);

            if (in_array('-c', $params_parts)) {
                $option_short = true;
                for ($i = 0; $i < $Counter; $i++) {
                    $history[$i] = " ";
                }
            } else if (in_array('-d', $params_parts)) {
                if (in_array(preg_match("[0-9]", $params_parts), $params_parts)) {
                    $option_short = true;
                    for ($i = 10; $i < $Counter; $i++) {
                        $history[$i] = " ";
                    }
                }
            }

        } else {
            for ($i = 0; $i < $Counter; $i++) {
                $sender->send("message|<br>" . ($i + 1) . " " . $history[$i]["command"]);
            }
        }
    }
}
