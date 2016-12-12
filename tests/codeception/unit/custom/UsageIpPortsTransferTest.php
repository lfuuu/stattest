<?php

namespace tests\codeception\unit\custom;

use app\helpers\DateTimeZoneHelper;
use Yii;
use DateTime;
use DateTimeZone;
use app\models\ClientAccount;
use app\models\UsageIpPorts;
use app\models\TariffInternet;
use app\models\LogTarif;
use app\models\usages\UsageInterface;

class UsageIpPortsTransferTest extends \yii\codeception\TestCase
{

    use _TransferTrait;
    use _TransferLogTariff;

    /**
     * Тест переноса услуг интернет
     */
    public function testUsageIpPorts()
    {
        $this->prepareTransfer(UsageIpPorts::className());

        $this->checkLogTariffAfter($this->fromUsage, $this->toUsage);

        $this->checkRoutesAfter($this->fromUsage, $this->toUsage);
        $this->checkDevicesAfter($this->fromUsage, $this->toUsage);
    }

    /**
     * @param UsageInterface $from
     * @param UsageInterface $to
     */
    private function checkRoutesAfter($from, $to)
    {
        $this->assertEquals(count($from->netList), count($to->netList), 'Routers is good');
    }

    /**
     * @param UsageInterface $from
     * @param UsageInterface $to
     */
    private function checkDevicesAfter($from, $to)
    {
        $this->assertEquals(count($from->cpeList), count($to->cpeList), 'Devices is good');
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $clientAccount
     * @param string $usageClass
     * @return int
     */
    private static function createSingleUsage(ClientAccount $clientAccount, $usageClass)
    {
        $tariffId = TariffInternet::find()->select('MAX(id)')->scalar();
        $actualFrom =
            (new DateTime('-1 week', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;

        $usage = new $usageClass;
        $usage->actual_from = $actualFrom;
        $usage->actual_to = $actualTo;
        $usage->client = $clientAccount->client;
        $usage->address = 'test address';
        $usage->save();

        $logTariff = new LogTarif;
        $logTariff->service = UsageIpPorts::tableName();
        $logTariff->id_service = $usage->id;
        $logTariff->id_tarif = $tariffId;
        $logTariff->date_activation = $usage->actual_from;
        $logTariff->save();

        return $usage->id;
    }

}