<?php

namespace IO;

class Console
{
    public static function ReadLine() : string
    {
        $result = fgets(STDIN);
        $result = str_replace("\n", "", $result);
        $result = str_replace("\r", "", $result);
        return $result;
    }
    
    public static function WriteLine(string $text) : void
    {
        self::Write($text . "\n");
    }
    
    public static function Write(string $text) : void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN")
        {
            $text = iconv("UTF-8", "CP866", $text);
        }
        echo $text;
    }
}