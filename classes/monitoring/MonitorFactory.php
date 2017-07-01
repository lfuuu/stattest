<?php

namespace app\classes\monitoring;

use app\classes\Assert;
use app\classes\Singleton;

/**
 * @method static MonitorFactory me($args = null)
 */
class MonitorFactory extends Singleton
{

    private function getMonitors()
    {
        return [
            $this->getUsagesLostTariffsMonitor(),
            $this->getMissingManagerMonitor(),
            $this->getUsagesIncorrectBusinessProcessStatus(),
            $this->getUsagesActiveConnecting(),
            $this->getUsagesOldReserve(),
            $this->getVoipNumbersIntegrity(),
            $this->getClientAccountWODayLimit(),
            $this->getUsageVoipNotFilledTariffs(),
            $this->getImportantEventsWithoutNames(),
            $this->getSyncErrorsAccounts(),
            $this->getSyncErrorsUsageVoip(),
            $this->getSyncErrorsUsageVpbx(),
            $this->getClientsOfSlovakia(),
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

        return null;
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
     * @return UsagesOldReserve
     */
    public function getUsagesOldReserve()
    {
        return new UsagesOldReserve();
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
     * @return UsageVoipNotFilledTariffs
     */
    public function getUsageVoipNotFilledTariffs()
    {
        return new UsageVoipNotFilledTariffs;
    }

    /**
     * @return ImportantEventsWithoutNames
     */
    public function getImportantEventsWithoutNames()
    {
        return new ImportantEventsWithoutNames();
    }

    /**
     * @return SyncErrorsAccounts
     */
    public function getSyncErrorsAccounts()
    {
        return new SyncErrorsAccounts();
    }

    /**
     * @return SyncErrorsUsageVoip
     */
    public function getSyncErrorsUsageVoip()
    {
        return new SyncErrorsUsageVoip();
    }

    /**
     * @return SyncErrorsUsageVpbx
     */
    public function getSyncErrorsUsageVpbx()
    {
        return new SyncErrorsUsageVpbx();
    }

    /**
     * @return ClientsOfSlovakia
     */
    public function getClientsOfSlovakia()
    {
        return new ClientsOfSlovakia();
    }

}