<?php
namespace app\dao;

use Yii;
use DateTime;
use DateTimeZone;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\Number;
use app\models\UsageVoip;
use app\models\TariffVoip;
use app\models\ClientAccount;

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

        $number->reserve_from = null;
        $number->reserve_till = null;
        $number->save();

        Number::dao()->toInstock($number);
    }

    public function startActiveStat(Number $number, UsageVoip $usage)
    {
        $number->client_id = $usage->clientAccount->id;
        $number->usage_id = $usage->id;
        $number->reserve_from = null;
        $number->reserve_till = null;
        $number->hold_from = null;
        $number->hold_to = null;
        $number->status = Number::STATUS_ACTIVE;
        $number->save();

        Number::dao()->log($number, 'invertReserved', 'Y');
    }

    public function stopActive(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_ACTIVE);

        $now = new DateTime('now', new DateTimeZone('UTC'));

        // Если тариф тестовый, то выкладываем номер минуя отстойник.
        $usage = UsageVoip::find()
            ->andWhere(['E164' => $number->number])
            ->andWhere(['<', 'actual_to', $now->format('Y-m-d')])
            ->orderBy('actual_to desc')
            ->limit(1)
            ->one();

        if (
            $usage
            && ($log = $usage->getCurrentLogTariff($usage->actual_from))
            && ($currentTariff = TariffVoip::findOne($log->id_tarif))
            && ($currentTariff->status == "test")
        ) {
            Number::dao()->toInstock($number);
            return ;
        }

        Number::dao()->startHold($number);
    }

    public function toInstock(Number $number)
    {
        $number->client_id = null;
        $number->usage_id = null;
        $number->hold_from = null;
        $number->hold_to = null;

        $number->status = Number::STATUS_INSTOCK;
        $number->save();

        Number::dao()->log($number, 'invertReserved', 'N');
    }

    public function startHold(Number $number, DateTime $holdTo = null)
    {
        Assert::isInArray($number->status, [Number::STATUS_INSTOCK, Number::STATUS_ACTIVE]);

        $number->client_id = null;
        $number->usage_id = null;
        $number->status = Number::STATUS_HOLD;

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $number->hold_from = $now->format("Y-m-d H:i:s");

        if ($holdTo) {
            $number->hold_to = $holdTo->format("Y-m-d H:i:s");
        } else {
            $now->modify("+6 month");
           $number->hold_to = $now->format("Y-m-d H:i:s");
        }

        $number->save();

        Number::dao()->log($number, 'hold');
    }

    public function stopHold(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_HOLD);

        $number->status = Number::STATUS_INSTOCK;
        $number->hold_to = null;
        $number->save();

        Number::dao()->log($number, 'unhold');
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

    public function actualizeStatusByE164($numbereE164)
    {
        $number = Number::findOne(['number' => $numbereE164]);
        if ($number) {
            $this->actualizeStatus($number);
        }
    }

    public function actualizeStatus(Number $number)
    {
        /** @var UsageVoip $usage */
        $usage = UsageVoip::find()
            ->andWhere(['E164' => $number->number])
            ->andWhere('actual_from<=DATE(now()) and actual_to >= DATE(now())')
            ->one();
        
        if ($usage) {
            if ($number->status != Number::STATUS_ACTIVE) {
                Number::dao()->startActiveStat($number, $usage);
            }
        } else {
            if ($number->status == Number::STATUS_ACTIVE) {
                Number::dao()->stopActive($number);
            }
        }
    }
}
