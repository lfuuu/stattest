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

    /**
     * ConsoleProgress constructor.
     * @param int $count
     * @param string $stepString
     * @param int $stepLength
     */
    public function __construct($count, $stepString = '. ', $stepLength = 50)
    {
        $this->count = $count;
        $this->stepString = $stepString;
        $this->stepLength = $stepLength;
        $this->index = $this->progress = 0;

        if ($count) {
            $this->printStep($this->stepLength);
            echo $count . PHP_EOL;
        }
    }

    /**
     * Печать шага
     *
     * @param int $size
     */
    protected function printStep($size = 1)
    {
        echo str_repeat('. ', $size);
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