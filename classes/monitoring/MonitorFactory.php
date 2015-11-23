<?php

namespace app\classes\monitoring;

use app\classes\Assert;
use app\classes\Singleton;

class MonitorFactory extends Singleton
{

    private function getMonitors()
    {
        return [
            $this->getUsagesLostTariffsMonitor(),
            $this->getMissingManagerMonitor(),
            $this->getUsagesIncorrectBusinessProcessStatus(),
            $this->getUsagesActiveConnecting(),
            $this->getVoipNumbersIntegrity(),
            $this->getClientAccountWODayLimit(),
            $this->getClientAccountDisabledCredit(),
        ];
    }

    /**
     * @param $monitorKey
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function getOne($monitorKey)
    {
        foreach ($this->getMonitors() as $monitor) {
            if ($monitor->key == $monitorKey) {
                return $monitor;
            }
        }
        Assert::isUnreachable('Monitor not found');
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $result = [];

        foreach ($this->getMonitors() as $monitor) {
            $result[$monitor->key] = $monitor;
        }

        return $result;
    }

    /**
     * @return UsagesLostTariffs
     */
    public function getUsagesLostTariffsMonitor()
    {
        return new UsagesLostTariffs;
    }

    /**
     * @return MissingManager
     */
    public function getMissingManagerMonitor()
    {
        return new MissingManager;
    }

    /**
     * @return UsagesIncorrectBusinessProcessStatus
     */
    public function getUsagesIncorrectBusinessProcessStatus()
    {
        return new UsagesIncorrectBusinessProcessStatus;
    }

    /**
     * @return UsagesActiveConnecting
     */
    public function getUsagesActiveConnecting()
    {
        return new UsagesActiveConnecting;
    }

    /**
     * @return VoipNumbersIntegrity
     */
    public function getVoipNumbersIntegrity()
    {
        return new VoipNumbersIntegrity;
    }

    /**
     * @return ClientAccountWODayLimit
     */
    public function getClientAccountWODayLimit()
    {
        return new ClientAccountWODayLimit;
    }

    /**
     * @return ClientAccountDisabledCredit
     */
    public function getClientAccountDisabledCredit()
    {
        return new ClientAccountDisabledCredit;
    }

}