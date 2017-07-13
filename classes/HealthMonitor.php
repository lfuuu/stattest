<?php
namespace app\classes;

use app\models\EventQueue;
use app\models\SyncPostgres;

/**
 * Class HealthMonitor
 */
class HealthMonitor extends Singleton
{
    const Z_SYNC_QUEUE_LENGTH = 'z_sync_queue_length';
    const QUEUE_PLANED = 'queue_planed';
    const SERVER_STATUS = 'server_status';
    const LOAD_AVERAGE = 'load_average';

    const MONITORS = [
        self::Z_SYNC_QUEUE_LENGTH,
        self::QUEUE_PLANED,
        self::LOAD_AVERAGE
    ];

    /**
     * Получаем значение по монитору
     *
     * @param string $needMonitor
     * @return array
     */
    public function getMonitorValues($needMonitor)
    {
        return $this->_getMonitorsValues([$needMonitor]);
    }

    /**
     * Получаем все отслеживаемые значения
     *
     * @return array
     */
    public function getMonitorsValues()
    {
        return $this->_getMonitorsValues(self::MONITORS);
    }

    /**
     * Получаем все отслеживаемые значения
     *
     * @param array $needMonitors
     * @return array
     */
    private function _getMonitorsValues($needMonitors)
    {
        $data = [];

        foreach ($needMonitors as $monitor) {

            $methodName = '_health_' . $monitor;

            if (!method_exists($this, $methodName)) {
                throw new \BadMethodCallException('Неизвестный метод: ' . $methodName);
            }

            $value = $this->{$methodName}();

            if ($value === false) {
                continue;
            }

            $data[$monitor] = $value;
        }

        return count($needMonitors) == 1 ? reset($data) : $data;
    }

    /**
     * Длина очереди на синхронизацию с биллингом
     *
     * @return int|string
     */
    private function _health_z_sync_queue_length()
    {
        return SyncPostgres::find()->count();
    }

    /**
     * Длина очереди событий
     *
     * @return int
     */
    private function _health_queue_planed()
    {
        return EventQueue::find()->where(['status' => EventQueue::STATUS_PLAN])->count();
    }

    /**
     * Состояние сервера
     *
     * @return string
     */
    private function _health_server_status()
    {
        $status = trim(exec('uptime'));

        // load average: 1,07, 0,82, 0,72 => load average: 1.07, 0.82, 0.72
        $status = str_replace( // нужна последовательность выполнения подмен
            [', ', ',', '@'],
            ['@', '.', ', '],
            $status);

        return $status;
    }

    /**
     * Получаем среднюю загрузку CPU за последнюю минуту
     */
    private function _health_load_average()
    {
        $serverStatus = $this->_health_server_status();

        if (preg_match_all('/load average: ([0-9.]+),/', $serverStatus, $matches) && isset($matches[1][0])) {
            return (float)$matches[1][0];
        }

        return false;
    }
}