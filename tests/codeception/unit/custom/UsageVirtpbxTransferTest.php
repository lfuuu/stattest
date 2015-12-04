<?php

namespace tests\codeception\unit\custom;

use Yii;
use DateTime;
use DateTimeZone;
use app\models\Region;
use app\models\UsageVoip;
use app\models\UsageVirtpbx;
use app\models\TariffVirtpbx;
use app\models\ClientAccount;
use app\models\usages\UsageInterface;
use app\models\LogTarif;
use tests\codeception\unit\models\_UsageVirtpbx;

class UsageVirtpbxTransferTest extends \yii\codeception\TestCase
{

    use _TransferTrait;
    use _TransferLogTariff;

    /**
     * Тест переноса услуги ВАТС
     */
    public function testUsageVirtpbxTransferWithoutNumbers()
    {
        list($fromUsage, $toUsage) = $this->checkTransfer(_UsageVirtpbx::className());

        $this->checkLogTariffAfter($fromUsage, $toUsage);
    }

    /**
     * Тест переноса услуги телефонии привязанной к ВАТС
     */
    public function testUsageVirtpbxTransferWithNumbersAndLines()
    {
        list($fromUsage, $toUsage) = $this->checkTransfer(_UsageVirtpbx::className(), function(UsageInterface $fromUsage) {
            $fromUsage->createUsageVoip();
        });

        $this->checkLogTariffAfter($fromUsage, $toUsage);

        foreach ($fromUsage->clientAccount->voipNumbers as $number => $options) {
            $voipUsage = UsageVoip::findOne(['E164' => $number, 'client' => $fromUsage->client, 'type_id' => 'number']);
            $this->assertNotNull($voipUsage, 'See object "VoipUsage" after transfer');

            $fromVoipUsage = $toVoipUsage = null;

            if ((int) $voipUsage->next_usage_id) {
                $fromVoipUsage = $voipUsage;
                $toVoipUsage = UsageVoip::findOne($voipUsage->next_usage_id);
            }
            else if ((int) $voipUsage->prev_usage_id) {
                $toVoipUsage = $voipUsage;
                $fromVoipUsage = UsageVoip::findOne($voipUsage->prev_usage_id);
            }

            $this->assertNotNull($fromVoipUsage, 'See object "fromVoipUsage" after transfer');
            $this->assertNotNull($toVoipUsage, 'See object "toVoipUsage" after transfer');

            $this->checkUsagesAfter($fromVoipUsage, $toVoipUsage);
            $this->checkLogTariffAfter($fromVoipUsage, $toVoipUsage);

            if ($toVoipUsage->line7800_id) {
                $fromLine7800 = UsageVoip::findOne($fromVoipUsage->line7800_id);
                $this->assertNotNull($toUsage, 'See object "fromVoipLine7800"');

                $toLine7800 = UsageVoip::findOne($toVoipUsage->line7800_id);
                $this->assertNotNull($toUsage, 'See object "toVoipLine7800"');

                $this->checkUsagesAfter($fromLine7800, $toLine7800);
            }
        }
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $client
     * @return int
     */
    private static function createSingleUsage(ClientAccount $client)
    {
        $tariffId = TariffVirtpbx::find()->select('MAX(id)')->scalar();
        $actualFrom = (new DateTime('-1 week', new DateTimeZone('UTC')))->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;
        $client = 'id' . $client->id;

        $usage = new UsageVirtpbx;
        $usage->region = Region::MOSCOW;
        $usage->actual_from = $actualFrom;
        $usage->actual_to = $actualTo;
        $usage->client = $client;
        $usage->tarif_id = $tariffId;
        $usage->save();

        $logTariff = new LogTarif;
        $logTariff->service = UsageVirtpbx::tableName();
        $logTariff->id_service = $usage->id;
        $logTariff->id_tarif = $tariffId;
        $logTariff->date_activation = $usage->actual_from;
        $logTariff->save();

        return $usage->id;
    }
}