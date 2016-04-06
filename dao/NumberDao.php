<?php
namespace app\dao;

use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use app\classes\Assert;
use app\classes\Singleton;
use app\models\Number;
use app\models\UsageVoip;
use app\models\TariffVoip;
use app\models\ClientAccount;
use app\models\TariffNumber;
use app\models\NumberLog;
use app\models\NumberType;
use app\models\billing\Calls;

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
                ->andWhere([
                    'status' => Number::STATUS_INSTOCK,
                    'number_type' => NumberType::ID_INTERNAL,
                    'did_group_id' => $didGroupId
                ])
                ->orderBy('RAND()')
                ->limit(1)
                ->one();
    }

    /**
     * @return Number
     */
    public function getRandomFree7800()
    {
        return
            Number::find()
                ->andWhere([
                    'status' => Number::STATUS_INSTOCK,
                    'number_type' => NumberType::ID_EXTERNAL,
                    'ndc' => '800'
                ])
                ->orderBy('RAND()')
                ->limit(1)
                ->one();
    }

    /**
     * @param string $region
     * @return ActiveRecord[]
     */
    public function getFreeNumbersByRegion($region = '')
    {
        return $this->getFreeNumbers()->andWhere([
            'region' => $region,
            'number_type' => NumberType::ID_INTERNAL
        ])->all();
    }

    /**
     * @param TariffNumber $tariff
     * @return ActiveRecord[]
     */
    public function getFreeNumbersByTariff(TariffNumber $tariff)
    {
        return $this->getFreeNumbers()->andWhere(['did_group_id' => $tariff->did_group_id])->all();
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

        Number::dao()->log($number, NumberLog::ACTION_INVERTRESERVED, 'Y');
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

        Number::dao()->log($number, NumberLog::ACTION_INVERTRESERVED, 'Y');
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
            && ($log = $usage->getLogTariff($usage->actual_from))
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

        Number::dao()->log($number, NumberLog::ACTION_INVERTRESERVED, 'N');
    }

    public function startHold(Number $number, DateTime $holdTo = null)
    {
        Assert::isInArray($number->status, [Number::STATUS_INSTOCK, Number::STATUS_ACTIVE]);

        $number->client_id = null;
        $number->usage_id = null;
        $number->status = Number::STATUS_HOLD;

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $number->hold_from = $now->format(DateTime::ATOM);

        if ($holdTo) {
            $number->hold_to = $holdTo->format(DateTime::ATOM);
        } else {
            $now->add($number->numberType->hold);
            $number->hold_to = $now->format(DateTime::ATOM);
        }

        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_HOLD);
    }

    public function stopHold(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_HOLD);

        $number->status = Number::STATUS_INSTOCK;
        $number->hold_to = null;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_UNHOLD);
    }

    public function startNotSell(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_INSTOCK);

        $number->client_id = 764;
        $number->status = Number::STATUS_NOTSELL;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_NOTSALE);
    }

    public function stopNotSell(Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_NOTSELL);

        $number->client_id = null;
        $number->status = Number::STATUS_INSTOCK;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_SALE);
    }

    public function log(Number $number, $action, $addition = null)
    {
        $row = new NumberLog();
        $row->e164 = $number->number;
        $row->action = $action;
        $row->client = $number->client_id;
        $row->user = Yii::$app->user->getId();
        $row->addition = $addition;
        $row->save();
    }

    public function actualizeStatusByE164($numberE164)
    {
        $number = Number::findOne(['number' => $numberE164]);
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

    public function getCallsWithoutUsages($region)
    {
        $dt = new \DateTime("now", new \DateTimeZone("UTC"));
        $dt->modify("first day of -3 month, 00:00:00");

        $query = Calls::find()
            ->select([
                'u' => 'dst_number',
                'c' => (new Expression('count(*)')),
                'm' => (new Expression("to_char(connect_time, 'MM')"))
            ])
            ->where(
                ['>', 'connect_time', $dt->format(DateTime::ATOM)]
            )
            ->andWhere([
                'server_id' => $region,
                'number_service_id' => null,
                'orig' => false
            ])
            ->groupBy([
                'u', 'm'
            ]);

        if ($region == 99) {
            $query->andWhere(
                ['between', 'dst_number', 74950000000, 74999000000]
            );
        }

        return $query
            ->createCommand()
            ->cache(86400)
            ->queryAll();
    }

    /**
     * @return ActiveQuery
     */
    public function getFreeNumbers()
    {
        return
            Number::find()
                ->where(['status' => Number::STATUS_INSTOCK])
                ->having(new Expression('
                    IF(
                        `number` LIKE "7495%",
                        `number` LIKE "74951059%" OR `number` LIKE "74951090%" OR `beauty_level` IN (1,2),
                        true
                    )
                '))
                ->orderBy([new Expression('IF(`beauty_level` = 0, 10, `beauty_level`) DESC, `number`')]);
    }

}
