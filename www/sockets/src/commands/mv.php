<?php
namespace Alph\Commands;

use Alph\Services\CommandAsset;
use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

/**
 * template = the name of the commands
 */
class mv implements CommandInterface
{
    /**
     * Command's usage
     */
    const USAGE = "mv [OPTION]... [-T] SOURCE DEST
    or:  mv [OPTION]... SOURCE... DIRECTORY
    or:  mv [OPTION]... -t DIRECTORY SOURCE...";

    /**
     * Command's short description
     */
    const SHORT_DESCRIPTION = "Rename SOURCE to DEST, or move SOURCE(s) to DIRECTORY.

    Mandatory arguments to long options are mandatory for short options too.";

    /**
     * Command's full description
     */
    const FULL_DESCRIPTION = "Rename SOURCE to DEST, or move SOURCE(s) to DIRECTORY.";

    /**
     * Command's options
     */
    const OPTIONS = [
        "-b" => "make a backup of each existing destination file, like --backup but does not accept an argument",
        "-f, --force" => "do not prompt before overwriting",
        "-i, --interactive" => "prompt before overwrite",
        "-n, --no-clobber" => "do not overwrite an existing file",
    ];

    /**
     * Command's exit status
     */
    const EXIT_STATUS = "Returns exit status of command or success if command is null.";

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
        // stock parameters for further treatment
        $registeredParameters = $parameters;

        // Treat command to get parameters
        $quotedParameters = CommandAsset::mvGetQuotedParameters($parameters, $data->position);
        $options = CommandAsset::getOptions($parameters);
        $pathParameters = CommandAsset::mvGetPathParameters($parameters, $data->position);

        $fullElements = explode(" ", $parameters);
        CommandAsset::concatenateParameters($fullElements, $quotedParameters, $pathParameters);

        //Check if element provided is more than 1
        if(count($fullElements) < 2){
            return $sender->send("message|<br>mv: target operand missing" . (count($fullElements) == 1 ? " after " . $fullElements[0] . "." : "."));
        }

        //get and remove target from parameters
        $target = CommandAsset::getTarget($registeredParameters, $fullElements);
        


        /*
    if (count($fullParameters) < 2) {
    return $sender->send("message|<br>mv: target operand missing" . (count($fullParameters) == 1 ? " after " . $fullParameters[0] . "." : "."));
    }

    $options = CommandAsset::mvGetOptions($fullParameters);

    $target = CommandAsset::getTarget($parameters, $fullParameters);

    // Transform element name into full path
    foreach ($fullParameters as $parameter) {
    $cleanedParameter = CommandAsset::cleanQuote($parameter);
    $absolutePathFullParameters[] = CommandAsset::getAbsolute($data->position, $cleanedParameter);
    }

    //Get Target Revelant information
    $cleanedTarget = CommandAsset::cleanQuote($target);
    $absolutePathTarget = CommandAsset::getAbsolute($data->position, $cleanedTarget);

    if(CommandAsset::getParentId($db, $terminal_mac, $absolutePathTarget)){
    $targetAttribut = CommandAsset::checkBoth($terminal_mac, $cleanedTarget, CommandAsset::getParentId($db, $terminal_mac, $absolutePathTarget), $db);
    } else {
    return;
    }

    // First check what will be the action depending on the target attribut

    // if Target is a directory
    if ($targetAttribut == 1) {
    foreach ($absolutePathFullParameters as $absolutePathParameter) {
    CommandAsset::updatePosition($db, $terminal_mac, $absolutePathParameter, CommandAsset::getIdDirectory($db, $terminal_mac, $absolutePathTarget), $absolutePathTarget, $sender);
    }

    }
     */
    }
}
