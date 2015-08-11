<?php
namespace app\dao;

use app\models\UsageVoip;
use Yii;
use app\classes\Assert;
use app\models\ClientAccount;
use DateTime;
use DateTimeZone;
use app\classes\Singleton;
use app\models\Number;

/**
 * @method static NumberDao me($args = null)
 * @property
 */
class NumberDao extends Singleton
{
    /**
     * @param $didGroupId
     * @return Number
     */
    public function getRandomFreeNumber($didGroupId)
    {
        return
            Number::find()
                ->andWhere(['status' => 'instock'])
                ->andWhere(['did_group_id' => $didGroupId])
                ->orderBy('RAND()')
                ->limit(1)
                ->one();
    }

    public function startReserve(Number $number, ClientAccount $clientAccount = null, DateTime $stopDate = null)
    {
        Assert::isEqual($number->status, Number::STATUS_INSTOCK);

        $utc = new DateTimeZone('UTC');

        $number->client_id = $clientAccount ? $clientAccount->id : null;
        $number->reserve_from = (new DateTime('now', $utc))->format('Y-m-d H:i:s');
        $number->reserve_till = $stopDate ? $stopDate->setTimezone($utc)->format('Y-m-d H:i:s') : null;
        $number->status = Number::STATUS_RESERVED;
        $number->save();

        Number::dao()->log($number, 'invertReserved', 'Y');
    }

    public function stopReserve(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_RESERVED);

        $number->client_id = null;
        $number->reserve_from = null;
        $number->reserve_till = null;
        $number->status = Number::STATUS_INSTOCK;
        $number->save();

        Number::dao()->log($number, 'invertReserved', 'N');
    }

    public function startActiveStat(Number $number, UsageVoip $usage)
    {
        if (!in_array($number->status, [Number::STATUS_INSTOCK, Number::STATUS_RESERVED])) {
            Assert::isUnreachable('Включить можно только свободный или зарезервированный номер');
        }

        $number->client_id = $usage->clientAccount->id;
        $number->usage_id = $usage->id;
        $number->reserve_from = null;
        $number->reserve_till = null;
        $number->hold_from = null;
        $number->status = Number::STATUS_ACTIVE;
        $number->save();

        Number::dao()->log($number, 'invertReserved', 'Y');
    }

    public function stopActive(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_ACTIVE);

        $now = new DateTime('now', new DateTimeZone('UTC'));

        $number->client_id = null;
        $number->usage_id = null;
        $number->hold_from = $now->format('Y-m-d H:i:s');
        $number->status = Number::STATUS_HOLD;
        $number->save();

        Number::dao()->log($number, 'invertReserved', 'N');
    }

    public function startHold(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_INSTOCK);

        $number->status = Number::STATUS_HOLD;
        $number->save();
    }

    public function stopHold(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_HOLD);

        $number->status = Number::STATUS_INSTOCK;
        $number->save();
    }

    public function startNotSell(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_INSTOCK);

        $number->client_id = 764;
        $number->status = Number::STATUS_NOT_SELL;
        $number->save();
    }

    public function stopNotSell(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_NOT_SELL);

        $number->client_id = null;
        $number->status = Number::STATUS_INSTOCK;
        $number->save();
    }

    public function log(Number $number, $action, $addition = null)
    {
        Yii::$app->db->createCommand("
            insert into `e164_stat` set `e164`=:number, action=:action, client=:clientId, user=:userId, addition=:addition;
        ", [
            ':number' => $number->number,
            ':action' => $action,
            ':clientId' => $number->client_id,
            ':userId' => Yii::$app->user->getId(),
            ':addition' => $addition,
        ])->execute();

    }
}