<?php

namespace tests\codeception\unit\custom;

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
        list($fromUsage, $toUsage) = $this->checkTransfer(UsageIpPorts::className());

        $this->checkLogTariffAfter($fromUsage, $toUsage);

        $this->checkRoutesAfter($fromUsage, $toUsage);
        $this->checkDevicesAfter($fromUsage, $toUsage);
    }

    private function checkRoutesAfter($from, $to)
    {
        $this->assertEquals(count($from->netList), count($to->netList), 'Routers is good');
    }

    private function checkDevicesAfter($from, $to)
    {
        $this->assertEquals(count($from->cpeList), count($to->cpeList), 'Devices is good');
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $client
     * @return int
     */
    private static function createSingleUsage(ClientAccount $client)
    {
        $tariffId = TariffInternet::find()->select('MAX(id)')->scalar();
        $actualFrom = (new DateTime('-1 week', new DateTimeZone('UTC')))->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;
        $client = 'id' . $client->id;

        $usage = new UsageIpPorts;
        $usage->actual_from  = $actualFrom;
        $usage->actual_to  = $actualTo;
        $usage->client = $client;
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