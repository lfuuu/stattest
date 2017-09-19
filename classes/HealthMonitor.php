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
    const QUEUE_STOPPED = 'queue_stopped';

    const MONITORS = [
        self::Z_SYNC_QUEUE_LENGTH,
        self::QUEUE_PLANED,
        self::QUEUE_STOPPED,
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
     * Длина очереди событий. Необработанные
     *
     * @return int
     */
    private function _health_queue_planed()
    {
        return EventQueue::find()->where(['status' => EventQueue::STATUS_PLAN])->count();
    }

    /**
     * Длина очереди событий. Ошибки
     *
     * @return int
     */
    private function _health_queue_stopped()
    {
        return EventQueue::find()->where(['status' => EventQueue::STATUS_STOP])->count();
    }
}