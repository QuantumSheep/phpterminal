<?php
namespace Alph\Services;

class Session
{
    public static function read(string $id)
    {
        return self::unserialize((string)@file_get_contents(DIR_SESS . 'sess_' . $id));
    }

    public static function write(\PDO $db, string $id, $data)
    {
        return \file_put_contents(DIR_SESS . 'sess_' . $id, $data) !== false;
    }

    public static function destroy(\PDO $db, string $id)
    {
        return unlink(DIR_SESS . 'sess_' . $id);
    }

    public static function unserialize(string $session_data)
    {
        $method = ini_get("session.serialize_handler");
        switch ($method) {
            case "php":
                return self::unserialize_php($session_data);
                break;
            case "php_binary":
                return self::unserialize_phpbinary($session_data);
                break;
            default:
                throw new Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php, php_binary");
        }
    }

    private static function unserialize_php(string $session_data)
    {
        $return_data = [];
        $offset = 0;

        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new Exception("Invalid data, remaining: " . substr($session_data, $offset));
            }
            
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }

        return $return_data;
    }

    private static function unserialize_phpbinary(string $session_data)
    {
        $return_data = [];
        $offset = 0;

        while ($offset < strlen($session_data)) {
            $num = ord($session_data[$offset]);
            $offset += 1;
            $varname = substr($session_data, $offset, $num);
            $offset += $num;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        
        return $return_data;
    }
}
