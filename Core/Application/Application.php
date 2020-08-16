<?php

namespace Application;

class Application
{
    public static function GetRequiredPhpVersion() : string
    {
        return __GET__APP()["php_version"];
    }
    
    public static function GetName() : string
    {
        return __GET__APP()["app_name"];
    }
    
    public static function GetDescription() : string
    {
        return __GET__APP()["app_description"];
    }
    
    public static function GetAuthor() : string
    {
        return __GET__APP()["app_author"];
    }
    
    public static function GetVersion() : string
    {
        return __GET__APP()["app_version"];
    }
    
    public static function GetExecutableFileName() : string
    {
        $v1 = \Phar::running(false);
        $v2 = __GET__FILE__();
        $r = (! empty($v1) ? $v1 : $v2);
        return $r;
    }
    
    public static function GetExecutableDirectory() : string
    {
        return dirname(self::GetExecutableFileName()) . DIRECTORY_SEPARATOR;
    }
}