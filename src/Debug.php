<?php
namespace Ch\Debug;

use Ch\Debug\Dumper\StreamDumper;
use Ch\Debug\Dumper\DumperInterface;

/**
 * Debugger
 *
 * It contains a stack of dumpers and validation by
 * remoteIp if this is provided.
 *
 * @author Wilson Champi <wchampi86@gmail.com>
 */
class Debug
{
    /**
     * The dumper stack
     *
     * @var DumperInterface[]
     */
    private static $dumpers = [];

    /**
     * Remote Ips
     *
     * @var array
     */
    private static $remoteIps = [];

    /**
     * Environment variable name
     *
     * @var string
     */
    private static $envName = 'DEBUG';

     /**
     * @param DumperInterface[] $dumpers   Optional stack of dumpers, the first one in the array is called first, etc.
     * @param array             $remoteIps Optional array of remoteIp, enable Debugging by IP
     * @param string            $envName   Optional name of environment variable, default is DEBUG
     */
    public static function setup(array $dumpers = [], array $remoteIps = [], $envName = 'DEBUG')
    {
        self::$dumpers = $dumpers;
        self::$remoteIps = $remoteIps;
        self::$envName = $envName;
    }

    /**
     * @param mix $value variable to dump
     * @param string $tag Optional tag to filter
     */
    public static function _($value, $tag = '')
    {
        $filter = self::getFilter();
        if ($filter != "") {
            $trace = self::getTrace($value, $tag);

            if (self::isValid($filter, $trace)) {
                return self::dump($trace);
            }
        }
        return true;
    }

    private static function getFilter()
    {
        $envName = self::$envName;

        if (self::validRemoteIp()) {
            if (isset($_GET[$envName]) && !empty($_GET[$envName])) {
                $filter = filter_var($_GET[$envName], FILTER_SANITIZE_STRING);
                @setcookie($envName, $filter, time() + (60 * 30), "/");
                return $filter;
            }

            if (isset($_COOKIE[$envName]) && !empty($_COOKIE[$envName])) {
                return filter_var($_COOKIE[$envName], FILTER_SANITIZE_STRING);
            }
        }

        return getenv($envName);
    }

    private static function getTrace($value, $tag)
    {
        $trace = debug_backtrace(2);

        $file = $trace[1]['file'];
        $line = $trace[1]['line'];
        $class = isset($trace[2]['class']) ? $trace[2]['class'] : '';
        $type = isset($trace[2]['type']) ? $trace[2]['type'] : '';
        $function = isset($trace[2]['function']) ? $trace[2]['function'] : '';

        $trace = [
            'remoteIp' => self::getRemoteIp(),
            'file' => $file,
            'line' => $line,
            'class' => $class,
            'type' => $type,
            'function' => $function,
            'args' => self::getArgs($file, $line, $tag),
            'value' => $value,
            'tag' => $tag
        ];

        return $trace;
    }

    private static function isValid($filter, $trace)
    {
        $pos = strpos("$trace[class]%$trace[function]%$trace[file]%$trace[tag]", $filter);

        if ($filter == '*' || $pos !== false) {
            return true;
        }
        return false;
    }

    private static function getArgs($file, $line, $tag)
    {
        $lines = file($file);
        $contentLine = $lines[$line - 1];

        $matches = [];
        preg_match('/::_\(([^"]*)\)/', $contentLine, $matches);

        $params = isset($matches[1]) ? trim($matches[1]) : '';
        if (!empty($params)) {
            $pattern = ['/,(\s+)\'(.*?)\'$/', '/,(\s+)\$(.*?)$/'];
            $params = preg_replace($pattern, ['', ''], $params);
        }

        return $params;
    }

    private static function getRemoteIp()
    {
        $ip = 'UNKNOW';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    private static function validRemoteIp()
    {
        if (count(self::$remoteIps) > 0) {
            $remoteIp = self::getRemoteIp();
            foreach (self::$remoteIps as $validIp) {
                $validIp = preg_replace('/\.\*/', '', $validIp);
                if (strpos($remoteIp, $ip) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    private static function dump($trace)
    {
        if (count(self::$dumpers) == 0) {
            self::$dumpers[] = new StreamDumper('php://stdout');
        }

        $result = true;
        foreach (self::$dumpers as $dumper) {
            if (!$dumper->dump($trace)) {
                $result = false;
            }
        }

        return $result;
    }
}
