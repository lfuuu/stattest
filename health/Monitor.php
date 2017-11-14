<?php

namespace app\health;


use yii\base\Component;

abstract class Monitor extends Component
{
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
    public static function getAvailableMonitors()
    {
        return [
            MonitorZSyncPostgres::className(),
            MonitorQueuePlanned::className(),
            MonitorQueueStopped::className(),
            MonitorQueueSwitchedOff::className(),
            // MonitorUuAccountEntry::className(),
            MonitorUuBill::className(),
            // MonitorUuTestTariff::className(), // менеджеры говорят, что пока это нормально
            MonitorUuShiftTariff::className(),
        ];
    }
}