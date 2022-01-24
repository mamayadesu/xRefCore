<?php

error_reporting(E_ALL);

$port = 0x0000;
$__CLASSNAME = "";
$__JSONNEWARGS = [];
$__PARENTPID = 0x0000;
/* {RANDOMKEY} */

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

$_APP = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "app.json"), true);
$__FILE__ = __FILE__;

including(__DIR__ . DIRECTORY_SEPARATOR . "Core");

$namespaces = $_APP["namespaces"];

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

spl_autoload_register(function(string $className)
{
    if (!class_exists($className))
    {
        require_once __DIR__ . DIRECTORY_SEPARATOR . $className . ".php";
    }
});

if (!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    exit;
}
$mypid = getmypid() . '';
if (!socket_connect($sock, '127.0.0.1', $port))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
    if ($errorcode != 0)
    {
        exit;
    }
}

$data = array('receivedpid' => $mypid);
$json = json_encode($data);
$length = strlen($json);
$lenstr = str_repeat("0", 16 - strlen($length . '')) . $length;

if (!socket_send($sock, $lenstr, 16, 0))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    if ($errorcode != 0)
    {
        exit;
    }
}

if (!socket_send($sock, $json, $length, 0))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);

    if ($errorcode != 0)
    {
        exit;
    }
}
new \Threading\__DataManager2($sock);

$__CLASSNAME::__SetParentThreadPid($__PARENTPID);
$thread = new $__CLASSNAME();
$thread->__setdata($sock, $port, new \Threading\ParentThreadedObject($sock, $port, $thread));
$thread->Threaded($__JSONNEWARGS);