<?php

namespace tests\codeception\unit\custom;

use Yii;
use DateTime;
use DateTimeZone;
use app\models\ClientAccount;
use app\models\TariffVoip;
use app\models\UsageVoip;
use app\models\usages\UsageInterface;
use app\models\LogTarif;

class UsageVoipTransferTest extends \yii\codeception\TestCase
{

    use _TransferTrait;
    use _TransferLogTariff;

    /**
     * Тест переноса услуги телефонии без линий
     */
    public function testUsageVoipTransferWithout7800()
    {
        list($fromUsage, $toUsage) = $this->checkTransfer(UsageVoip::className());

        $this->checkLogTariffAfter($fromUsage, $toUsage);
    }

    /**
     * Тест переноса услуги телефонии с линией
     */
    public function testUsageVoipTransferWith7800()
    {
        list($fromUsage, $toUsage) = $this->checkTransfer(UsageVoip::className());

        // Проверка результата переноса линий
        $fromLine7800 = UsageVoip::findOne($fromUsage->line7800_id);
        $this->assertNotNull($toUsage, 'See object "fromLine7800"');

        $toLine7800 = UsageVoip::findOne($toUsage->line7800_id);
        $this->assertNotNull($toUsage, 'See object "toLine7800"');

        $this->checkUsagesAfter($fromLine7800, $toLine7800);
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $client
     * @return int
     */
    private static function createSingleUsage(ClientAccount $client, $is7800 = false)
    {
        $tariffId = TariffVoip::find()->select('MAX(id)')->scalar();
        $actualFrom = (new DateTime('-1 week', new DateTimeZone('UTC')))->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;
        $client = 'id' . $client->id;
        $line7800_id = 0;

        if ($is7800) {
            $line7800 = new UsageVoip;
            $line7800->actual_from = $actualFrom;
            $line7800->actual_to = $actualTo;
            $line7800->client = $client;
            $line7800->type_id = 'line';
            $line7800->address = 'test address line 7800';
            $line7800->save();
            $line7800_id = $line7800->id;

            $logTariff = new LogTarif;
            $logTariff->service = UsageVoip::tableName();
            $logTariff->id_service = $line7800->id;
            $logTariff->id_tarif = $tariffId;
            $logTariff->date_activation = $line7800->actual_from;
            $logTariff->save();
        }

        $usage = new UsageVoip;
        $usage->actual_from  = $actualFrom;
        $usage->actual_to  = $actualTo;
        $usage->client = $client;
        $usage->address = 'test address';
        $usage->line7800_id = $line7800_id;
        $usage->save();

        $logTariff = new LogTarif;
        $logTariff->service = UsageVoip::tableName();
        $logTariff->id_service = $usage->id;
        $logTariff->id_tarif = $tariffId;
        $logTariff->date_activation = $usage->actual_from;
        $logTariff->save();

        return $usage->id;
    }
}