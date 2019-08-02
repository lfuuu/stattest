<?php

namespace app\modules\uu\classes;

use yii\log\Logger;
use yii\log\Target;

/**
 * Класс для сбора статистики по запросам к БД
 */
class QueryCounterTarget extends Target
{
    protected $queries = [];
    protected $count = 0;
    protected $time = 0;

    public $categories = ['yii\db\Command::query', 'yii\db\Command::execute'];
    public $logVars = [];

    /**
     * Calculates the elapsed time for the given log messages.
     * @return array timings. Each element is an array consisting of these elements:
     * `info`, `category`, `timestamp`, `trace`, `level`, `duration`, `memory`, `memoryDiff`.
     */
    protected function calculateTimings()
    {
        if (empty($this->messages)) {
            return [];
        }

        $timings = [];
        $stack = [];
        $left = [];

        foreach ($this->messages as $i => $log) {
            list($token, $level, $category, $timestamp, $traces) = $log;
            $memory = isset($log[5]) ? $log[5] : 0;
            $log[6] = $i;
            $hash = md5(json_encode($token));
            if ($level == Logger::LEVEL_PROFILE_BEGIN) {
                $stack[$hash] = $log;
                $left[$i] = $log;
            } elseif ($level == Logger::LEVEL_PROFILE_END) {
                if (isset($stack[$hash])) {
                    $timings[$stack[$hash][6]] = [
                        'info' => $stack[$hash][0],
                        'category' => $stack[$hash][2],
                        'timestamp' => $stack[$hash][3],
                        'trace' => $stack[$hash][4],
                        'level' => count($stack) - 1,
                        'duration' => $timestamp - $stack[$hash][3],
                        'memory' => $memory,
                        'memoryDiff' => $memory - (isset($stack[$hash][5]) ? $stack[$hash][5] : 0),
                    ];
                    $left[$stack[$hash][6]] = null;
                    unset($stack[$hash]);
                }
            }
        }

        ksort($timings);

        $this->messages = array_filter($left);

        return $timings;
    }

    /**
     * @inheritdoc
     */
    public function export()
    {
        if ($timings = $this->calculateTimings()) {
            $this->count += count($timings);

            foreach ($timings as $timing) {
                $this->time += $timing['duration'];
                $this->queries[] = $timing['info'];
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function collect($messages, $final)
    {
        foreach (static::filterMessages($messages, $this->getLevels(), $this->categories, $this->except) as $message) {
            $this->messages[] = $message;
        }

        $count = count($this->messages);
        if ($count > 0 && ($final || $this->exportInterval > 0 && $count >= $this->exportInterval)) {
            $this->export();
        }

        return;
    }

    /**
     * Сбросить статистику
     */
    public function reset()
    {
        $this->messages = [];
        $this->queries = [];
        $this->count = 0;
        $this->time = 0;
    }

    /**
     * Получить статистику
     *
     * @return array
     */
    public function getStat()
    {
        $this->export();

        $duplicates = array_count_values($this->queries);
        $duplicates = array_filter($duplicates, function ($value) {
            return $value > 1;
        });
        $duplicates = array_sum($duplicates) - count($duplicates);

        return [
            $this->count,
            $this->time,
            $duplicates
        ];
    }
}