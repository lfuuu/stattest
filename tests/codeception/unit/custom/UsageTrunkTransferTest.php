<?php

namespace tests\codeception\unit\custom;

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
        list($fromUsage, $toUsage) = $this->checkTransfer(UsageTrunk::className());

        $this->assertEquals(count($fromUsage->settings), count($toUsage->settings), 'Settings is good');
    }

    /**
     * Создание болванки услуги для переноса
     * @param ClientAccount $client
     * @return int
     */
    private static function createSingleUsage(ClientAccount $client, $usageClass)
    {
        $actualFrom = (new DateTime('-1 week', new DateTimeZone('UTC')))->format('Y-m-d');
        $actualTo = UsageInterface::MAX_POSSIBLE_DATE;

        $usage = new $usageClass;
        $usage->actual_from  = $actualFrom;
        $usage->actual_to  = $actualTo;
        $usage->client_account_id = $client->id;
        $usage->connection_point_id = Region::MOSCOW;
        $usage->save();

        return $usage->id;
    }

}