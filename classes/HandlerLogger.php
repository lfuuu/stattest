<?php
/**
 * Нужен для временного (в пределах процесса) сохранения некоторых данных. Фактически аналог global-переменной.
 * Удобно использовать для логирования результатов API-запросов в очередь событий.
 */

namespace app\classes;

/**
 * @method static HandlerLogger me()
 */
class HandlerLogger extends Singleton
{
    /** @var array */
    private array $_logs = ['main' => []];

    /**
     * Очистить лог
     */
    public function clear($category = 'main')
    {

        $this->_logs[$category] = [];
    }

    /**
     * Добавить лог
     *
     * @param string $log
     */
    public function add($log, $category = 'main')
    {
        if (!$log) {
            return;
        }

        if (!isset($this->_logs[$category])) {
            $this->_logs[$category] = [];
        }
        $this->_logs[$category][] = $log;
    }

    public function __toString()
    {
        return implode(PHP_EOL, $this->get());
    }

    /**
     * Вернуть логи
     *
     * @return string[]
     */
    public function get($category = 'main')
    {
        return $this->_logs[$category] ?: [];
    }


    function echoToLog(\Closure $cb, $category = 'main')
    {
        ob_start();
        try {
            $result = $cb();
            if ($log = ob_get_clean()) {
                echo ' ' . $log;
                $this->add($log, $category);
            }
            ob_end_clean();
            return $result;
        } catch (\Exception $e) {
            if ($log = ob_get_clean()) {
                $this->add($log, $category);
            }
            ob_end_clean();

            throw $e;
        }
    }

}