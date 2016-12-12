<?php

namespace tests\codeception\unit\custom;

use app\helpers\DateTimeZoneHelper;
use Yii;
use DateTime;
use DateTimeZone;
use app\models\Region;
use app\models\ClientAccount;
use app\models\UsageTrunk;
use app\models\UsageTrunkSettings;
use app\models\usages\UsageInterface;

class UsageTrunkTransferTest extends \yii\codeception\TestCase
{

    use _TransferTrait;

    /**
     * Тест переноса услуги телефония транки
     */
    public function testUsageTrunk()
    {
        $this->prepareTransfer(UsageTrunk::className());

        $this->assertEquals(count($this->fromUsage->settings), count($this->toUsage->settings), 'Settings is good');
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $clientAccount
     * @param string $usageClass
     * @return int
     */
    private static function createSingleUsage(ClientAccount $clientAccount, $usageClass)
    {
        $actualFrom =
            (new DateTime('-1 week', new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;

        $usage = new $usageClass;
        $usage->actual_from = $actualFrom;
        $usage->actual_to = $actualTo;
        $usage->client_account_id = $clientAccount->id;
        $usage->connection_point_id = Region::MOSCOW;

        if ($usage instanceof UsageTrunk) {
            $usage->trunk_id = 0;
            $usage->operator_id = 0;
        }

        $usage->save();

        return $usage->id;
    }

}