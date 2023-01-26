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
            MonitorZSyncPostgres::class,
            MonitorQueue::class,
            MonitorQueueSwitchedOff::class,
            MonitorSuperClientStruct::class,
            BacklogSlaveServer::class,
            MonitorNnpPrefix::class,
            MonitorSormClientsReg88::class,
            MonitorBrokenRegionInVoip::class,
            Monitor1cTroubles::class,
            MonitorSorm_Habarovsk::class,
            MonitorSorm_Krasnoiarsk::class,
            MonitorMultipleEnabledNumbers::class,
            MonitorDoubleService::class,
            MonitorNotificationScriptHungOn::class,
        ];
    }

    /**
     * @return array
     */
    public static function getAvailableHeavyMonitors()
    {
        return [
            MonitorUuBill::class,
            // MonitorUuShiftTariff::class,
            // MonitorUuAccountEntry::class, // обычно работает долю секунды, но иногда лочится надолго
            MonitorUuTestTariff::class,
            // MonitorSormClientsReg97::class, // до введения СОРМа в Краснодаре, отключим монитор. Что бы Борис не расстраивался.
            MonitorVoipDelayOnAccountTariffs::class,
            MonitorTariffsWithoutLogs::class,
            MonitorWrongNumberRegion::class,
            MonitorTariffSync::class,
            MonitorDoublePayment::class,
            MonitorMultiAccountService::class,
            MonitorVoipDelayOnPackages::class,
            MonitorRouteMncOperator::class,
        ];
    }
}
