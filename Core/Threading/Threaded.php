<?php

namespace Threading;

use IO\Console;

/**
 * Provides information and access to child thread
 * @package Threading
 */

class Threaded
{
    /**
     * @var int
     * @ignore
     */
    private int $child, $port;

    /**
     * @var array
     * @ignore
     */
    private array $args;

    /**
     * @var string
     * @ignore
     */
    private string $className, $command;

    /**
     * @var ChildThreadedObject|null
     * @ignore
     */
    private ?ChildThreadedObject $cto;

    /**
     * @var null
     * @ignore
     */
    private $sock = null;

    /**
     * @var object
     * @ignore
     */
    private object $handler;

    /**
     * @var bool
     * @ignore
     */
    private bool $threadshutdown = false;

    /**
     * Threaded constructor.
     * @param int $childPid
     * @param array $args
     * @param string $className
     * @param $sock
     * @param int $port
     * @param object $handler
     * @ignore
     */
    public function __construct(int $childPid, array $args, string $className, $sock, int $port, object $handler)
    {
        $this->child = $childPid;
        $this->args = $args;
        $this->className = $className;
        $this->cto = new ChildThreadedObject($sock, $port, $this);
        $this->sock = $sock;
        $this->handler = $handler;
        $this->port = $port;
    }

    /**
     * Provides access to public methods and properties of threaded child class
     *
     * @return ChildThreadedObject|null
     */
    public function GetChildThreadedObject() : ?ChildThreadedObject
    {
        return $this->cto;
    }

    /**
     * Returns PID of child thread
     *
     * @return int PID of child thread
     */
    public function GetChildPid() : int
    {
        return $this->child;
    }

    /**
     * Returns list of arguments passed by the parent thread
     *
     * @return array<int, string> Arguments passed by the parent thread
     */
    public function GetArguments() : array
    {
        return $this->args;
    }

    /**
     * Returns name of threaded class
     *
     * @return string Full name of threaded class
     */
    public function GetClassName() : string
    {
        return $this->className;
    }

    /**
     * Returns TRUE if child thread still running
     *
     * @return bool TRUE if thread still running. FALSE thread is closed by any reason
     */
    public function IsRunning() : bool
    {
        if ($this->threadshutdown)
        {
            return false;
        }
        $result = false;
        if (Thread::IsWindows())
        {
            exec("tasklist /FI \"PID eq " . $this->child . "\" /FO csv | find /c /v \"\"", $output1);
            $output = rtrim(str_replace(" ", "", $output1[0]));
            $linesCount = intval($output);
            $result = $linesCount > 1;
        }
        else
        {
            $result = file_exists("/proc/" . $this->child);
        }
        if (!$result)
        {
            $this->threadshutdown = true;
        }
        return $result;
    }

    /**
     * Waits for the child thread to begin interacting with the parent thread. The parent thread will be frozen and wait for the child thread to finish synchronizing
     *
     * @return bool|void Returns false if child thread is closed
     */
    public function WaitForChildAccess()
    {
        if ($this->threadshutdown)
        {
            trigger_error("Cannot synchronize with thread, because thread is closed", E_USER_WARNING);
            return false;
        }
        $__dm = __DataManager1::GetInstance();
        while (true)
        {
            $q = $__dm->__Read($this->port);
            while (true)
            {
                if (!isset($q["act"]))
                {
                    $q = $__dm->__Continue();
                }
                else
                {
                    $q = $__dm->__Fetch();
                    break;
                }
            }
            
            $result = null;
            switch ($q["act"])
            {
                case "c":
                    $result = call_user_func_array(array($this->handler, $q["method"]), $q["args"]);
                    break;
                    
                case "g":
                    $result = $this->handler->{$q["prop"]};
                    break;

                case "s":
                    $this->handler->{$q["prop"]} = $q["val"];
                    break;

                case "threadstop":
                    $this->threadshutdown = true;
                    return;
                    break;

                case "sy":
                    return;
                    break;
            }

            $type = strtolower(gettype($result));
            if ($type == "null")
            {
                $type = "void";
                $result = "";
            }
            else if ($type == "integer")
            {
                $type = "int";
            }
            else
            {
                if (!self::check($result))
                {
                    trigger_error("Method result can be only void, string, integer, array, boolean, float, double or long", E_USER_WARNING);
                    $type = "void";
                    $result = "";
                }
            }

            $query = array
            (
                "event" => $q["event"],
                "t" => $type,
                "r" => $result
            );
            $json = json_encode($query);
            if (!socket_sendto($this->sock, self::LengthToString(strlen($json)), 16, 0, "127.0.0.1", $this->port))
            {
                //
            }
            if (!socket_sendto($this->sock, $json, strlen($json), 0, "127.0.0.1", $this->port))
            {
                //
            }
        }
    }

    /**
     * Stop synchronization with child thread
     */
    public function FinishSychnorization() : void
    {
        $query = array
        (
            "act" => "sy"
        );
        $json = json_encode($query);
        if (!socket_sendto($this->sock, self::LengthToString(strlen($json)), 16, 0, "127.0.0.1", $this->port))
        {
            if (!$this->IsRunning())
            {
                exit;
            }
            else
            {
                trigger_error("Failed to access data from threaded class (1)", E_USER_WARNING);
            }
            return;
        }
        if (!socket_sendto($this->sock, $json, strlen($json), 0, "127.0.0.1", $this->port))
        {
            if (!$this->IsRunning())
            {

            }
            else
            {
                trigger_error("Failed to access data from threaded class (2)", E_USER_WARNING);
            }
            return;
        }
    }

    /**
     * @param $a
     * @return bool
     * @ignore
     */
    private static function check($a) : bool
    {
        return is_string($a) || is_int($a) || is_array($a) || is_bool($a) || is_float($a) || is_double($a) || is_long($a);
    }

    /**
     * @param int $length
     * @return string
     * @ignore
     */
    private static function LengthToString(int $length) : string
    {
        return str_repeat("0", 16 - strlen($length . "")) . $length;
    }

    /**
     * Kills child thread
     */
    public function Kill() : void
    {
        if ($this->sock == null)
        {
            return;
        }
        if (Thread::IsWindows())
        {
            pclose(popen("taskkill /F /PID " . $this->child, "r"));
        }
        else
        {
            exec("kill -9 " . $this->child);
        }
    }
}