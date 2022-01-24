<?php

error_reporting(E_ALL);

$phar_file = \Phar::running(false);
if ($phar_file == "")
{
    $file = basename(__FILE__);
    if ($file != "autoload_dev.php")
    {
        die("The name of autoload file must be 'autoload_dev.php'");
    }
}

echo "Initializing...\n";

$_ALREADY_REGISTERED = [];
$_QUEUE = [];
function including($path)
{
    global $_ALREADY_REGISTERED, $_QUEUE;
    $regex = "/Class \'(.*?)\' not found/";
    $regex1 = "/Interface \'(.*?)\' not found/";
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
                if (in_array($obj1, $_ALREADY_REGISTERED))
                {
                    continue;
                }
                echo "Registering " . $obj1 . "\n";
                try
                {
                    require_once $obj1;
                }
                catch (\Throwable $e)
                {
                    $msg = $e->getMessage();
                    if (preg_match($regex, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex, "$1", $msg);
                    }
                    else if (preg_match($regex1, $msg))
                    {
                        $missingClass = "\\" . preg_replace($regex1, "$1", $msg);
                    }
                    else
                    {
                        die($e->getMessage());
                    }
                    if (!isset($_QUEUE[$missingClass]))
                    {
                        $_QUEUE[$missingClass] = [];
                    }
                    echo "Package " . $obj1 . " is missing '" . $missingClass . "'. Waiting when this class will be loaded...\n";
                    $_QUEUE[$missingClass][$obj1] = $obj1;
                }
                if (count($_QUEUE) > 0)
                {
                    foreach ($_QUEUE as $notLoadedClass => $value)
                    {
                        if (class_exists($notLoadedClass))
                        {
                            foreach ($_QUEUE[$notLoadedClass] as $queueFileName => $value)
                            {
                                unset($_QUEUE[$notLoadedClass][$queueFileName]);
                                echo "'" . $notLoadedClass . "' was loaded! Trying to register " . $queueFileName . "\n";
                                try
                                {
                                    require_once $queueFileName;
                                }
                                catch (\Throwable $e)
                                {
                                    $msg = $e->getMessage();
                                    if (preg_match($regex, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex, "$1", $msg);
                                    }
                                    else if (preg_match($regex1, $msg))
                                    {
                                        $missingClass1 = "\\" . preg_replace($regex1, "$1", $msg);
                                    }
                                    else
                                    {
                                        die($e->getMessage());
                                    }
                                    if (!isset($_QUEUE[$missingClass1]))
                                    {
                                        $_QUEUE[$missingClass1] = [];
                                    }
                                    echo "And now package " . $obj1 . " is missing '" . $missingClass . "'. Okay... waiting when this class will be loaded...\n";
                                    $_QUEUE[$missingClass1][$obj1] = $obj1;
                                }
                            }
                        }
                    }
                }
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

if (count($_QUEUE) > 0)
{
    echo "Next packages could not be loaded:\n";
    foreach ($_QUEUE as $notLoadedClass)
    {
        foreach ($_QUEUE[$notLoadedClass] as $notLoadedPackage)
        {
            echo $notLoadedPackage . " is missing " . $notLoadedClass;
        }
    }
    die(255);
}

echo "Reading app.json\n";

$_APP = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "app.json"), true);
$__FILE__ = __FILE__;
var_dump($_APP);

echo "Checking PHP-version\n";
if (version_compare(phpversion(), $_APP["php_version"], '<'))
{
    die($_APP["app_name"] . " " . $_APP["app_version"] . " requires at least PHP " . $_APP["php_version"] . ". Your version of PHP is " . phpversion());
}

echo "Loading core...\n";
including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

$namespaces = $_APP["namespaces"];

echo "Setting title\n";
\Application\Application::SetTitle($_APP["app_name"]);

function __GET__APP()
{
    global $_APP;
    return $_APP;
}

function __GET__FILE__()
{
    global $__FILE__;
    return $__FILE__;
}

function __GET_FRAMEWORK_VERSION()
{
    return "1.8.0.0";
}

echo "Loading classes...\n";
foreach ($namespaces as $ns)
{
    if ($ns == "Program")
    {
        echo "Don't insert 'Program' into namespaces. It is deprecated\n";
        continue;
    }
    including(__DIR__ . DIRECTORY_SEPARATOR . $ns);
}

including(__DIR__ . DIRECTORY_SEPARATOR . "Program");

$args = [];

echo "Reading on-start args...\n";
if (isset($argv))
{
    $args = $argv;
}

var_dump($args);

echo "Starting application...\n";
new \Program\Main($args);