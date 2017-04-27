<?php

namespace tests\codeception\unit\custom;

use app\helpers\DateTimeZoneHelper;
use app\models\Region;
use Yii;
use DateTime;
use DateTimeZone;
use app\models\ClientAccount;
use app\models\TariffVoip;
use app\models\UsageVoip;
use tests\codeception\unit\models\_UsageVoip;
use app\models\usages\UsageInterface;
use app\models\LogTarif;
use app\modules\uu\models\Tariff;

class UsageVoipTransferTest extends \yii\codeception\TestCase
{

    use _TransferTrait;
    use _TransferLogTariff;

    /**
     * Тест переноса услуги телефонии без линий
     */
    public function testUsageVoipTransferWithout7800()
    {
        $this->prepareTransfer(UsageVoip::className());
        $this->checkLogTariffAfter($this->fromUsage, $this->toUsage);
    }

    /**
     * Тест переноса услуги телефонии 7800 с линией
     */
    public function testUsageVoipTransferWith7800()
    {
        $this->prepareTransfer(_UsageVoip::className(), function (_UsageVoip $fromUsage) {
            $fromUsage->setLine7800();
        });

        $fromLine7800 = UsageVoip::findOne($this->fromUsage->line7800_id);
        $this->assertNotNull($fromLine7800, 'See object "fromLine7800"');

        $toLine7800 = UsageVoip::findOne($this->toUsage->line7800_id);
        $this->assertNotNull($toLine7800, 'See object "toLine7800"');

        $this->checkUsagesAfter($fromLine7800, $toLine7800);
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $clientAccount
     * @param string $usageClass
     * @return int
     */
    private static function createSingleUsage(ClientAccount $clientAccount, $usageClass)
    {
        $tariffId = TariffVoip::find()->select('MAX(id)')->scalar();
        $actualFrom =
            (new DateTime('-1 week', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;

        $usage = new $usageClass;
        $usage->actual_from = $actualFrom;
        $usage->actual_to = $actualTo;
        $usage->client = $clientAccount->client;
        $usage->type_id = Tariff::NUMBER_TYPE_NUMBER;
        $usage->E164 = '123456' . mt_rand(0, 9);
        $usage->address = 'test address';
        $usage->region = Region::MOSCOW;
        $usage->create_params = '';
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