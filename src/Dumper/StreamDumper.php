<?php
namespace Ch\Debug\Dumper;

class StreamDumper extends AbstractDumper
{
    private $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function dump($trace)
    {
        $formatted = sprintf(
            "\033[1;37m%s \033[0;32m%s\033[0m\n",
            $this->getFormattedVarLocation($trace),
            preg_replace("/[\n](.*)/", "\n\033[0;32m$1", $this->getFormattedVar($trace))
        );
        return (bool)file_put_contents($this->stream, $formatted);
    }
}
