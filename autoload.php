<?php

error_reporting(E_ALL);

function including($path)
{
    $output = "";
    $data = scandir($path);
    $splitFileName = [];
    $ext = "";
    $obj1 = "";
    $toNextIncluding = [];
    foreach ($data as $obj)
    {
        if ($obj == "." || $obj == "..")
        {
            continue;
        }
        $obj1 = $path . DIRECTORY_SEPARATOR . $obj;
        if (is_file($obj1))
        {
            $splitFileName = explode(".", $obj);
            if (count($splitFileName) < 2)
            {
                continue;
            }
            $ext = $splitFileName[count($splitFileName) - 1];
            if (strtolower($ext) == "php")
            {
                require_once $obj1;
            }
        }
        else
        {
            $toNextIncluding[] = $obj1;
        }
    }
    foreach($toNextIncluding as $obj1)
    {
        including($obj1);
    }
}

$_APP = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "app.json"), true);

if (version_compare(phpversion(), $_APP["php_version"], '<'))
{
    die($_APP["app_name"] . " " . $_APP["app_version"] . " requies at least PHP " . $_APP["php_version"] . ". Your version of PHP is " . phpversion());
}

including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

$namespaces = $_APP["namespaces"];

function __GET__APP()
{
    global $_APP;
    return $_APP;
}

foreach ($namespaces as $ns)
{
    including(__DIR__ . DIRECTORY_SEPARATOR . $ns);
}

$args = [];

if (isset($argv))
{
    $args = $argv;
}

new Program\Main($args);