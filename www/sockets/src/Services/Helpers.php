<?php
namespace Alph\Services;

class Helpers
{
    public static function getAbsolute(string...$path)
    {
        $absolute = "";
    
        $absolute_parts = [];
    
        if (count($path) <= 0) {
            return "/";
        }
    
        if ($path[0][0] !== '/') {
            throw new \Exception("The first path given to getAbsolute function must be an absolute path.");
        }
    
        $i = 0;
    
        foreach ($path as $p) {
            $part = explode('/', $p);
            foreach ($part as $partofpart) {
                if ($part == "" && $i == 0) {
                    $absolute_parts = [];
                    $i = 0;
                } else if ($partofpart == ".") {
                    $i--;
                } else if ($partofpart == "..") {
                    if (!isset($absolute_parts[$i])) {
                        throw new Exception("Wrong path value.");
                        return false;
                    }
    
                    array_splice($absolute_parts, $i, 1);
    
                    $i--;
                } else {
                    $absolute_parts[] = $partofpart;
                    $i++;
                }
            }
        }
    
        for ($j = 0; $j <= $i; $j++) {
            if ($absolute_parts[$j] == "") {
                \array_splice($absolute_parts, $j, 1);
            }
        }
    
        var_dump($absolute_parts);
    
        return '/' . join('/', $absolute_parts);
    }
}
