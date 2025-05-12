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
    /** @var string[] */
    private $_logs = [];

    /**
     * Очистить лог
     */
    public function clear()
    {
        $this->_logs = [];
    }

    /**
     * Добавить лог
     *
     * @param string $log
     */
    public function add($log)
    {
        if (!$log) {
            return;
        }

        $this->_logs[] = $log;
    }

    /**
     * Вернуть логи
     *
     * @return string[]
     */
    public function get()
    {
        return $this->_logs;
    }


    function echoToLog(\Closure $cb)
    {
        ob_start();
        try {
            $result = $cb();
            if ($log = ob_get_clean()) {
                echo ' ' . $log;
                $this->add($log);
            }
            ob_end_clean();
            return $result;
        }catch (\Exception $e) {
            if ($log = ob_get_clean()) {
                $this->add($log);
            }
            ob_end_clean();

            throw $e;
        }
    }

}