<?php

namespace app\commands;

use app\classes\HealthMonitor;
use app\helpers\DateTimeZoneHelper;
use yii\console\Controller;
use yii\helpers\Json;

/**
 * Class HealthController
 */
class HealthController extends Controller
{
    const MONITOR_ICON_CONFIG = [
        HealthMonitor::QUEUE_PLANED => [10, 20, 30], // warning, critical, error
        HealthMonitor::LOAD_AVERAGE => [4, 5, 8],
        HealthMonitor::Z_SYNC_QUEUE_LENGTH => [5, 10, 20]
    ];

    const STATUS_OK = 'STATUS_OK';
    const STATUS_WARNING = 'STATUS_WARNING';
    const STATUS_CRITICAL = 'STATUS_CRITICAL';
    const STATUS_ERROR = 'STATUS_ERROR';

    const HEALTH_JSON_FILE_PATH = '@app/web/operator/_private/health.json';

    const EXTERNAL_JSON_FILES = [
        'healthSync' => 'http://eridanus.mcn.ru/health/healthSync.json',
        'healthFullSync' => 'http://eridanus.mcn.ru/health/healthFullSync.json',
    ];

    /**
     * Сбор счетчиков
     */
    public function actionIndex()
    {
        $monitorValues = HealthMonitor::me()->getMonitorsValues();
        foreach ($monitorValues as $healthType => $healthValue) {
            $this->_logHeals($healthType, $healthValue);
        }

        $this->_makeHealthIconJsonFile($monitorValues);
    }

    /**
     * Логирование
     *
     * @param string $healsType
     * @param string|int $value
     */
    private function _logHeals($healsType, $value)
    {
        $message = 'Heals for ' . $healsType . ': ' . $value;
        \Yii::info($message, 'heals');
        echo PHP_EOL . date(DateTimeZoneHelper::DATETIME_FORMAT) . ': ' . $message;
    }

    /**
     * Создание JSON файла для иконки мониторинга
     *
     * @param int[] $monitorValues
     */
    private function _makeHealthIconJsonFile($monitorValues)
    {
        $data = [
            'instanceId' => 200,
            'extendedInfo' => 'Statistic for ' . (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT)
        ];

        $config = self::MONITOR_ICON_CONFIG;

        $count = 0;
        foreach ($monitorValues as $type => $value) {

            if (!isset($config[$type])) {
                continue;
            }

            $status = $this->_getStatus($config[$type], $value);
            $data['item' . $count++] = [
                'itemId' => $type,
                'itemVal' => $value,
                'statusId' => $status,
                'statusMessage' => $type . ' is ' . $value,
            ];
        }

        $datetimeYesterday = (new \DateTime())->modify('-1 day')->format(DateTimeZoneHelper::DATETIME_FORMAT);

        // Добавить внешние JSON
        foreach (self::EXTERNAL_JSON_FILES as $jsonKey => $jsonUrl) {
            $jsonString = file_get_contents($jsonUrl);
            if (!$jsonString) {
                $data['item' . $count++] = [
                    'itemId' => $jsonKey,
                    'itemVal' => 0,
                    'statusId' => self::STATUS_ERROR,
                    'statusMessage' => "{$jsonKey} недоступен",
                ];
                continue;
            }

            $jsonArray = json_decode($jsonString, $assoc = true);
            if (!$jsonArray) {
                $data['item' . $count++] = [
                    'itemId' => $jsonKey,
                    'itemVal' => 0,
                    'statusId' => self::STATUS_ERROR,
                    'statusMessage' => "{$jsonKey} невалидный",
                ];
                continue;
            }

            if (!$jsonArray['timestamp'] || $jsonArray['timestamp'] < $datetimeYesterday) {
                $data['item' . $count++] = [
                    'itemId' => $jsonKey,
                    'itemVal' => 0,
                    'statusId' => self::STATUS_ERROR,
                    'statusMessage' => "{$jsonKey} неактуальный",
                ];
                continue;
            }

            $data['item' . $count++] = [
                'itemId' => $jsonArray['itemId'],
                'itemVal' => $jsonArray['itemVal'],
                'statusId' => $jsonArray['statusId'],
                'statusMessage' => $jsonArray['statusMessage'],
            ];
        }

        $filePath = \Yii::getAlias(self::HEALTH_JSON_FILE_PATH);

        if (!is_writable($filePath)) {
            throw new \RuntimeException('Невозможно записать файл мониторинга (' . $filePath . ')');
        }

        file_put_contents($filePath, Json::encode($data));
    }

    /**
     * Получение статуса по значению монитора
     *
     * @param int[] $config
     * @param float|int $value
     * @return string
     */
    private function _getStatus($config, $value)
    {
        if ($value >= $config[2]) { // error
            return self::STATUS_ERROR;
        }

        if ($value >= $config[1]) { // critical
            return self::STATUS_CRITICAL;
        }

        if ($value >= $config[0]) { // warning
            return self::STATUS_WARNING;
        }

        return self::STATUS_OK;
    }

}
