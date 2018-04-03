<?php

namespace app\commands;

use app\health\Monitor;
use app\helpers\DateTimeZoneHelper;
use InvalidArgumentException;
use ReflectionClass;
use Yii;
use yii\console\Controller;
use yii\helpers\Json;

class HealthController extends Controller
{
    const STATUS_OK = 'STATUS_OK';
    const STATUS_WARNING = 'STATUS_WARNING';
    const STATUS_CRITICAL = 'STATUS_CRITICAL';
    const STATUS_ERROR = 'STATUS_ERROR';

    /**
     * Метод сбора счетчиков с высоким приоритетом, вызываемый по умолчанию
     *
     * @throws \ReflectionException
     */
    public function actionIndex()
    {
        $this->actionLight();
    }
    
    /**
     * Сбор счетчиков с высоким приоритетом
     *
     * @throws \ReflectionException
     */
    public function actionLight()
    {
        $data = [];
        $lightMonitors = Monitor::getAvailableLightMonitors();
        $this->_collectData($data, $this->_getInternalClassesData($lightMonitors));
        $this->_collectData($data, $this->_getExternalUrlsData());
        $this->_exportData($data);
    }

    /**
     * Сбор счетчиков с низким приоритетом
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \yii\base\InvalidParamException
     * @throws \ReflectionException
     */
    public function actionHeavy()
    {
        $data = [];
        $heavyMonitors = Monitor::getAvailableHeavyMonitors();
        $this->_collectData($data, $this->_getInternalClassesData($heavyMonitors));
        foreach ($data as $group => $content) {
            foreach ((array)$content as $key => $value) {
                $filename = \Yii::getAlias("@app/web/export/health/{$value['itemId']}.json");
                if(!file_put_contents($filename, Json::encode($value))) {
                    throw new \RuntimeException('Невозможно записать файл мониторинга ' . $filename);
                }
            }
        }
    }

    /**
     * Логирование
     *
     * @param string $itemId
     * @param int $itemValue
     */
    private function _logHealth($itemId, $itemValue)
    {
        $message = 'Health for ' . $itemId . ': ' . $itemValue;
        \Yii::info($message, 'health');
        echo date(DateTimeZoneHelper::DATETIME_FORMAT) . ': ' . $message . PHP_EOL;
    }

    /**
     * Группировка данных
     *
     * @param array $collectData
     * @param array $data
     */
    private function _collectData(&$collectData, $data)
    {
        $configExport = Yii::$app->params['health']['export'];

        foreach ($data as $monitorGroup => $monitorData) {
            $group = isset($configExport[$monitorGroup]) ? $monitorGroup : Monitor::DEFAULT_MONITOR_GROUP;
            foreach ($monitorData as $value) {
                $idx = isset($collectData[$group]) ? count($collectData[$group]) : 0;
                $collectData[$group]['item' . $idx] = $value;
            }
        }
    }

    /**
     * Выгрузка данных в файл
     *
     * @param array $data
     */
    private function _exportData($data)
    {
        $configExport = Yii::$app->params['health']['export'];

        foreach ($data as $monitorGroup => $monitorData) {
            $group = isset($configExport[$monitorGroup]) ? $monitorGroup : Monitor::DEFAULT_MONITOR_GROUP;

            $dataHeader = [
                'instanceId' => Monitor::INSTANCE[$group],
                'extendedInfo' => 'Statistic for ' . (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
                        ->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ];

            $filePath = \Yii::getAlias($configExport[$group]);
            if(!file_put_contents($filePath, Json::encode($monitorData + $dataHeader))) {
                throw new \RuntimeException('Невозможно записать файл мониторинга ' . $filePath);
            }
        }
    }

    /**
     * Добавить внутренние JSON
     *
     * @param array $internalClasses
     * @return array
     * @throws \ReflectionException
     */
    private function _getInternalClassesData(array $internalClasses)
    {
        $data = [];

        foreach ($internalClasses as $className) {

            if (!class_exists($className, true)) {
                throw new InvalidArgumentException('Class ' . $className . ' does not exists');
            }

            /** @var Monitor $monitor */
            $monitor = new $className;

            $itemId = (new ReflectionClass($monitor))->getShortName();
            $itemId = str_replace('Monitor', '', $itemId);
            $itemValue = $monitor->getValue();
            $limits = $monitor->getLimits();

            $data[$monitor->monitorGroup][] = [
                'itemId' => $itemId,
                'itemVal' => $itemValue,
                'statusId' => $this->_getStatus($limits, $itemValue),
                'statusMessage' => method_exists($monitor, 'getMessage') ? $monitor->getMessage() : $itemValue,
            ];
            $this->_logHealth($itemId, $itemValue);
        }

        return $data;
    }

    /**
     * Добавить внешние JSON
     */
    private function _getExternalUrlsData()
    {
        $data = [];

        $datetimeYesterday = (new \DateTime())
            ->modify('-1 day')
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        /** @var string[] $externalUrls */
        $externalGroupsUrls = Yii::$app->params['health']['externalUrls'];
        foreach ($externalGroupsUrls as $monitorGroup => $externalUrls) {
            $data[$monitorGroup] = [];
            foreach ($externalUrls as $jsonKey => $jsonUrl) {
                $jsonString = @file_get_contents(\Yii::getAlias($jsonUrl));
                if (!$jsonString) {
                    $data[$monitorGroup][] = [
                        'itemId' => $jsonKey,
                        'itemVal' => 0,
                        'statusId' => self::STATUS_WARNING,
                        'statusMessage' => "{$jsonKey} недоступен",
                    ];
                    $this->_logHealth($jsonKey, 'недоступен');
                    continue;
                }

                $jsonArray = json_decode($jsonString, $assoc = true);
                if (!$jsonArray) {
                    $data[$monitorGroup][] = [
                        'itemId' => $jsonKey,
                        'itemVal' => 0,
                        'statusId' => self::STATUS_WARNING,
                        'statusMessage' => "{$jsonKey} невалидный",
                    ];
                    $this->_logHealth($jsonKey, 'невалидный');
                    continue;
                }

                if (!array_key_exists('timestamp', $jsonArray) || $jsonArray['timestamp'] < $datetimeYesterday) {
                    $data[$monitorGroup][] = [
                        'itemId' => $jsonKey,
                        'itemVal' => 0,
                        'statusId' => self::STATUS_WARNING,
                        'statusMessage' => "{$jsonKey} неактуальный",
                    ];
                    $this->_logHealth($jsonKey, 'неактуальный');
                    continue;
                }

                $data[$monitorGroup][] = [
                    'itemId' => $jsonArray['itemId'],
                    'itemVal' => $jsonArray['itemVal'],
                    'statusId' => $jsonArray['statusId'],
                    'statusMessage' => $jsonArray['statusMessage'],
                ];
                $this->_logHealth($jsonKey, $jsonArray['itemVal']);
            }
        }

        return $data;
    }

    /**
     * Получение статуса по значению монитора
     *
     * @param int[] $limits
     * @param float|int $value
     * @return string
     */
    private function _getStatus($limits, $value)
    {
        if ($value >= $limits[2]) { // error
            return self::STATUS_ERROR;
        }

        if ($value >= $limits[1]) { // critical
            return self::STATUS_CRITICAL;
        }

        if ($value >= $limits[0]) { // warning
            return self::STATUS_WARNING;
        }

        return self::STATUS_OK;
    }

}
