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
}