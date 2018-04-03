<?php

namespace app\health;


use yii\base\Component;

abstract class Monitor extends Component
{
    const ERROR_EXECUTE_VALUE = 999999;

    const GROUP_MAIN = 'main';
    const GROUP_FOR_MANAGERS = 'for_managers';

    const DEFAULT_MONITOR_GROUP = self::GROUP_MAIN;

    const INSTANCE = [
        self::GROUP_MAIN => 200,
        self::GROUP_FOR_MANAGERS => 201,
    ];

    public $monitorGroup = self::GROUP_MAIN;

    /**
     * Текущее значение
     *
     * @return int
     */
    abstract public function getValue();

    /**
     * 3 значения, начиная с которого Warning/Critical/Error
     *
     * @return int[]
     */
    public function getLimits()
    {
        return [1, 10, 50];
    }

    /**
     * @return string[]
     */
    public static function getAvailableLightMonitors()
    {
        return [
            MonitorZSyncPostgres::className(),
            MonitorQueue::className(),
            MonitorQueueSwitchedOff::className(),
            MonitorSuperClientStruct::className(),
            BacklogSlaveServer::className(),
            MonitorNnpPrefix::className(),
            MonitorSormClientsReg88::className(),
            MonitorBrokenRegionInVoip::className(),
            Monitor1cTroubles::className(),
        ];
    }

    /**
     * @return array
     */
    public static function getAvailableHeavyMonitors()
    {
        return [
            MonitorUuBill::className(),
            MonitorUuShiftTariff::className(),
            MonitorUuAccountEntry::className(),
            MonitorUuTestTariff::className(),
            MonitorSormClientsReg97::className(),
        ];
    }
}
