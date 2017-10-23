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

    private $_i = 0;

    /**
     * Сбор счетчиков
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \yii\base\InvalidParamException
     * @throws \ReflectionException
     */
    public function actionIndex()
    {
        $data = [
            'instanceId' => 200,
            'extendedInfo' => 'Statistic for ' . (new \DateTime('now', new \DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT)
        ];

        // Добавить внутренние JSON
        $data += $this->_getInternalClassesData();

        // Добавить внешние JSON
        $data += $this->_getExternalUrlsData();

        $filePath = \Yii::getAlias(Yii::$app->params['health']['export']);
        if (!is_writable($filePath)) {
            throw new \RuntimeException('Невозможно записать файл мониторинга ' . $filePath);
        }

        file_put_contents($filePath, Json::encode($data));
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
     * Добавить внутренние JSON
     *
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    private function _getInternalClassesData()
    {
        $data = [];

        $internalClasses = Monitor::getAvailableMonitors();
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

            $data['item' . $this->_i++] = [
                'itemId' => $itemId,
                'itemVal' => $itemValue,
                'statusId' => $this->_getStatus($limits, $itemValue),
                'statusMessage' => $itemId . ' is ' . $itemValue,
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
        $externalUrls = Yii::$app->params['health']['externalUrls'];
        foreach ($externalUrls as $jsonKey => $jsonUrl) {
            $jsonString = @file_get_contents($jsonUrl);
            if (!$jsonString) {
                $data['item' . $this->_i++] = [
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
                $data['item' . $this->_i++] = [
                    'itemId' => $jsonKey,
                    'itemVal' => 0,
                    'statusId' => self::STATUS_WARNING,
                    'statusMessage' => "{$jsonKey} невалидный",
                ];
                $this->_logHealth($jsonKey, 'невалидный');
                continue;
            }

            if (!$jsonArray['timestamp'] || $jsonArray['timestamp'] < $datetimeYesterday) {
                $data['item' . $this->_i++] = [
                    'itemId' => $jsonKey,
                    'itemVal' => 0,
                    'statusId' => self::STATUS_WARNING,
                    'statusMessage' => "{$jsonKey} неактуальный",
                ];
                $this->_logHealth($jsonKey, 'неактуальный');
                continue;
            }

            $data['item' . $this->_i++] = [
                'itemId' => $jsonArray['itemId'],
                'itemVal' => $jsonArray['itemVal'],
                'statusId' => $jsonArray['statusId'],
                'statusMessage' => $jsonArray['statusMessage'],
            ];
            $this->_logHealth($jsonKey, $jsonArray['itemVal']);
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
