<?php

namespace app\classes\contragent\importer\lk;

use Closure;
use Generator;

class BufferedLoader
{
    private $limit = 1000;

    private array $buffer = [];

    private ?Generator $srcGenerator;
    private Closure $fn;

    public function __construct(Generator $srcGenerator, float $limit = null)
    {
        if ($limit) {
            $this->limit = $limit;
        }

        $this->srcGenerator = $srcGenerator;
    }

    public function setPostLoader(Closure $fn): BufferedLoader
    {
        $this->fn = $fn;

        return $this;
    }

    public function getGenerator(): Generator
    {
        do {
            $isLoaded = false;
            if (!$this->buffer) {
                $this->loadBuffer();
                $isLoaded = true;
            }

            $nextValue = array_shift($this->buffer);
            if ($nextValue) {
                yield $nextValue;
            }

        } while ($nextValue || !$isLoaded);
    }

    private function loadBuffer()
    {
        for ($i = 0; $i < $this->limit; $i++) {
            $value = $this->srcGenerator->current();
            $this->srcGenerator->next();
            if (!$value) {
                break;
            }
            $this->buffer[] = $value;
        }

        if ($this->buffer && isset($this->fn)) {
            $f = $this->fn;
            $f($this->buffer);
        }
    }
}
