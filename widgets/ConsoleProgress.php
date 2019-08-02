<?php

namespace app\widgets;

/**
 * Прогресс выполнения
 */
class ConsoleProgress
{
    protected $count;
    protected $stepString;
    protected $stepLength;
    protected $index;
    protected $progress;
    /** @var \Closure */
    protected $outputCallback;

    /**
     * ConsoleProgress constructor.
     * @param int $count
     * @param string $stepString
     * @param int $stepLength
     */
    public function __construct($count, \Closure $outputCallback = null, $stepString = '. ', $stepLength = 50)
    {
        $this->count = $count;
        $this->stepString = $stepString;
        $this->stepLength = $stepLength;
        $this->index = $this->progress = 0;
        if ($outputCallback) {
            $this->outputCallback = $outputCallback;
        }

        if ($count) {
            $this->printStep($this->stepLength);
            $this->output($count . PHP_EOL);
        }
    }

    /**
     * Печать шага
     *
     * @param int $size
     */
    protected function printStep($size = 1)
    {
        $this->output(
            str_repeat($this->stepString, $size)
        );
    }

    /**
     * Вывод
     *
     * @param $string
     */
    protected function output($string)
    {
        if ($this->outputCallback) {
            $closure = $this->outputCallback;
            $closure($string);
        } else {
            echo $string;
        }
    }

    /**
     * Переходим на следующий шаг
     */
    public function nextStep()
    {
        if ($this->progress >= $this->stepLength) {
            return;
        }

        $currentPercent = intval(floor(++$this->index * $this->stepLength) / $this->count);
        if ($this->progress != $currentPercent) {
            $this->printStep($currentPercent - $this->progress);

            $this->progress = $currentPercent;
        }
    }

}