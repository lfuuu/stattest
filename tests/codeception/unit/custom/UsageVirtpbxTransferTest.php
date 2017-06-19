<?php

namespace tests\codeception\unit\custom;

use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\LogTarif;
use app\models\Region;
use app\models\TariffVirtpbx;
use app\models\usages\UsageInterface;
use app\models\UsageVirtpbx;
use app\models\UsageVoip;
use DateTime;
use DateTimeZone;
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
        $this->prepareTransfer(_UsageVirtpbx::className());

        $this->checkLogTariffAfter($this->fromUsage, $this->toUsage);
    }
    /**
     * Тест переноса услуги телефонии привязанной к ВАТС
     */
    public function testUsageVirtpbxTransferWithNumbersAndLines()
    {
        $this->prepareTransfer(_UsageVirtpbx::className(),
            function (_UsageVirtpbx $fromUsage) {
                $fromUsage->createUsageVoip();
            });

        $this->checkLogTariffAfter($this->fromUsage, $this->toUsage);
     }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $clientAccount
     * @param string $usageClass
     * @return int
     */
    private static function createSingleUsage(ClientAccount $clientAccount, $usageClass)
    {
        $tariffId = TariffVirtpbx::find()->select('MAX(id)')->scalar();
        $actualFrom =
            (new DateTime('-1 week', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;

        $usage = new $usageClass;
        $usage->region = Region::MOSCOW;
        $usage->actual_from = $actualFrom;
        $usage->actual_to = $actualTo;
        $usage->client = $clientAccount->client;
        $usage->tarif_id = $tariffId;
        $usage->moved_from = 0;
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