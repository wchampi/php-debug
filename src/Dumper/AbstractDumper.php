<?php
namespace Ch\Debug\Dumper;

abstract class AbstractDumper implements DumperInterface
{
    private function exceptionToString($e)
    {
        return $e->getMessage() . " in " . $e->getFile() . "(" . $e->getLine() . ")\n\n" . $e->getTraceAsString();
    }

    public function getFormattedVar($trace)
    {
        $value = $trace['value'];
        $isObject = (is_array($value) || is_object($value));
        $params = empty($trace['args']) ? '' : "$trace[args] = ";
        $tag = empty($trace['tag']) ? '' : ($isObject ? "" : ' ') . "[$trace[tag]]";

        if ($isObject) {
            if ($value instanceof \Exception) {
                $valueDump = $this->exceptionToString($value);
            } else {
                $overloadVarDump = ini_get('xdebug.overload_var_dump');
                ini_set('xdebug.overload_var_dump', 1);

                ob_start();
                var_dump($value);
                $valueDump = ob_get_clean();

                ini_set('xdebug.overload_var_dump', $overloadVarDump);
            }
        } elseif ($value === null) {
            $valueDump = 'NULL';
        } else {
            $valueDump = $value;
        }

        $regexs = "/=>[\r\n\s]+/";
        $replace = " => ";
        $valueDump = preg_replace($regexs, $replace, $valueDump);

        return sprintf(
            '%s%s%s',
            $params,
            $valueDump,
            $tag
        );
    }

    public function getFormattedVarLocation($trace)
    {
        $function = empty($trace['function']) ? '' : "$trace[function]()";
        $region = $trace['class'] . $trace['type'] . $function;
        $region = empty($region) ? '' : " $region";

        return sprintf(
            "%s(%s)%s:",
            $trace['file'],
            $trace['line'],
            $region
        );
    }
}
