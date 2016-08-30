<?php
namespace app\dao;

use app\classes\Assert;
use app\classes\Singleton;
use app\classes\uu\model\AccountTariff;
use app\classes\uu\model\AccountTariffLog;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\Calls;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\Number;
use app\models\NumberLog;
use app\models\TariffVoip;
use app\models\UsageVoip;
use DateTime;
use DateTimeZone;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * @method static NumberDao me($args = null)
 * @property
 */
class NumberDao extends Singleton
{

    public function startReserve(\app\models\Number $number, ClientAccount $clientAccount = null, DateTime $stopDate = null)
    {
        Assert::isEqual($number->status, Number::STATUS_INSTOCK);

        $utc = new DateTimeZone('UTC');

        $number->client_id = $clientAccount ? $clientAccount->id : null;
        $number->reserve_from = (new DateTime('now', $utc))->format('Y-m-d H:i:s');
        $number->reserve_till = $stopDate ? $stopDate->setTimezone($utc)->format('Y-m-d H:i:s') : null;
        $number->status = Number::STATUS_NOTACTIVE_RESERVED;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_INVERTRESERVED, 'Y');
    }

    public function stopReserve(\app\models\Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_NOTACTIVE_RESERVED);

        $number->reserve_from = null;
        $number->reserve_till = null;
        $number->save();

        Number::dao()->toInstock($number);
    }

    public function startActive(\app\models\Number $number, UsageVoip $usage = null, AccountTariff $uuUsage = null)
    {

        if ($usage) {
            $number->usage_id = $usage->id;
            $number->client_id = $usage->clientAccount->id;
            $newStatus = $usage->tariff->isTest() ? Number::STATUS_ACTIVE_TESTED : Number::STATUS_ACTIVE_COMMERCIAL;
        } else { //uuUsage
            $number->uu_account_tariff_id = $uuUsage->id;
            $number->client_id = $uuUsage->client_account_id;
            $newStatus = $uuUsage->tariffPeriod->tariff->isTest ? Number::STATUS_ACTIVE_TESTED : Number::STATUS_ACTIVE_COMMERCIAL;
        }

        if ($newStatus == $number->status) {
            return;
        }

        $number->reserve_from = null;
        $number->reserve_till = null;
        $number->hold_from = null;
        $number->hold_to = null;
        $number->status = $newStatus;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_ACTIVE, $newStatus == Number::STATUS_ACTIVE_TESTED ? NumberLog::ACTION_ADDITION_TESTED : NumberLog::ACTION_ADDITION_COMMERCIAL);
    }

    public function stopActive(\app\models\Number $number)
    {
        if (!in_array($number->status, Number::$statusGroup[Number::STATUS_GROUP_ACTIVE])) {
            return;
        }

        $now = new DateTime('now', new DateTimeZone('UTC'));

        // Если тариф тестовый, то выкладываем номер минуя отстойник.
        /** @var UsageVoip $usage */
        $usage = UsageVoip::find()
            ->andWhere(['E164' => $number->number])
            ->andWhere(['<', 'actual_to', $now->format('Y-m-d')])
            ->orderBy('actual_to desc')
            ->limit(1)
            ->one();

        /** @var AccountTariff $accountTariff */
        $accountTariff = AccountTariff::find()
            ->where([
                'voip_number' => $number->number,
                'tariff_period_id' => null
            ])
            ->orderBy(['update_time' => SORT_DESC])
            ->one();

        /** @var AccountTariffLog $accountTariffLog */
        if ($accountTariff) {
            $accountTariffLog = $accountTariff->getActiveAccountTariffLog();
        }

        $clientAccount = null;
        if ($usage && (!$accountTariff || ($accountTariffLog && $accountTariffLog->actual_from < $usage->actual_to))) {
            $accountTariff = null;
            $clientAccount = $usage->clientAccount;
        } else if ($accountTariff && $accountTariffLog && (!$usage || $accountTariffLog->actual_from > $usage->actual_to)) {
            $usage = null;
            $clientAccount = $accountTariff->clientAccount;
        } else {
            $usage = $accountTariff = null;
        }

        if ($clientAccount) {
            if ($clientAccount->contract->business_process_status_id == BusinessProcessStatus::TELEKOM_MAINTENANCE_TRASH) {
                Number::dao()->toInstock($number);
                return;
            }
        }

        if ($usage) {
            if ($usage->tariff->isTest()) {
                Number::dao()->toInstock($number);
                return;
            }
        }

        if ($accountTariff) {
            $isLastTariffTest = false;
            foreach($accountTariff->getAccountTariffLogs()->each() as $accountTariffLog) {
                if ($accountTariffLog->tariff_period_id) {
                    $isLastTariffTest = $accountTariffLog->tariffPeriod->tariff->isTest;
                    break;
                }
            }
            if ($isLastTariffTest) {
                Number::dao()->toInstock($number);
                return;
            }
        }

        Number::dao()->startHold($number);
    }

    public function toInstock(\app\models\Number $number)
    {
        $number->client_id = null;
        $number->usage_id = null;
        $number->uu_account_tariff_id = null;
        $number->hold_from = null;
        $number->hold_to = null;

        $number->status = Number::STATUS_INSTOCK;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_INVERTRESERVED, 'N');
    }

    public function toRelease(\app\models\Number $number)
    {
        $number->client_id = null;
        $number->usage_id = null;
        $number->uu_account_tariff_id = null;
        $number->hold_from = null;
        $number->hold_to = null;

        $number->status = Number::STATUS_RELEASED;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_CREATE, 'N');
    }

    public function startHold(\app\models\Number $number, DateTime $holdTo = null)
    {
        Assert::isInArray($number->status, array_merge([Number::STATUS_INSTOCK, Number::STATUS_NOTACTIVE_HOLD], Number::$statusGroup[Number::STATUS_GROUP_ACTIVE]));

        $number->client_id = null;
        $number->usage_id = null;
        $number->uu_account_tariff_id = null;
        $number->status = Number::STATUS_NOTACTIVE_HOLD;

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $number->hold_from = $now->format(DateTimeZoneHelper::DATETIME_FORMAT);

        if ($holdTo) {
            $number->hold_to = $holdTo->format(DateTimeZoneHelper::DATETIME_FORMAT);
        } else {
            $now->add($number->numberType->hold);
            $number->hold_to = $now->format(DateTimeZoneHelper::DATETIME_FORMAT);
        }

        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_HOLD);
    }

    public function stopHold(\app\models\Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_NOTACTIVE_HOLD);

        $number->status = Number::STATUS_INSTOCK;
        $number->hold_to = null;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_UNHOLD);
    }

    public function startNotSell(\app\models\Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_INSTOCK);

        $number->client_id = 764;
        $number->status = Number::STATUS_NOTSALE;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_NOTSALE);
    }

    public function stopNotSell(\app\models\Number $number)
    {
        Assert::isEqual($number->status, Number::STATUS_NOTSALE);

        $number->client_id = null;
        $number->status = Number::STATUS_INSTOCK;
        $number->save();

        Number::dao()->log($number, NumberLog::ACTION_SALE);
    }

    public function log(\app\models\Number $number, $action, $addition = null)
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

    public function actualizeStatus(\app\models\Number $number)
    {
        /** @var UsageVoip $usage */
        $usage = UsageVoip::find()
            ->andWhere(['E164' => $number->number])
            ->actual()
            ->one();

        $uuUsage = null;

        if (!$usage) {
            $uuUsage = AccountTariff::find()
                ->where(['voip_number' => $number])
                ->andWhere(['not', ['tariff_period_id' => null]])
                ->one();
        }

        if ($usage) {
            Number::dao()->startActive($number, $usage);
        }elseif($uuUsage) {
            Number::dao()->startActive($number, null, $uuUsage);
        } else {
            Number::dao()->stopActive($number);
        }
    }

    /**
     * Статистика по звонкам за последние 3 месяца
     *
     * @param int $region
     * @param int $dstNumber
     * @return array
     */
    public function getCallsWithoutUsages($region, $dstNumber = null)
    {
        return $this->getCallsWithoutUsagesQuery($region, $dstNumber)
            ->createCommand()
            ->cache(86400)
            ->queryAll();
    }

    /**
     * Статистика по звонкам за заданный период (если не задано, то за 3 последних месяца)
     *
     * @param int $region
     * @param int $dstNumber
     * @param \DateTime|\DateTimeImmutable $dtFrom
     * @param \DateTime|\DateTimeImmutable $dtTo
     * @return ActiveQuery
     */
    public function getCallsWithoutUsagesQuery($region, $dstNumber = null, $dtFrom = null, $dtTo = null)
    {
        if (!$dtFrom) {
            $dtFrom = new \DateTime("now", new \DateTimeZone("UTC"));
            $dtFrom->modify("first day of -3 month, 00:00:00");
        }

        $query = Calls::find()
            ->select([
                'u' => 'dst_number',
                'c' => (new Expression('count(*)')),
                'm' => (new Expression("to_char(connect_time, 'MM')"))
            ])
            ->andWhere([
                'number_service_id' => null,
                'orig' => false
            ])
            ->groupBy([
                'u',
                'm'
            ]);

        $region && $query->andWhere(['server_id' => $region]);
        $dstNumber && $query->andWhere(['dst_number' => $dstNumber]);

        if ($dtTo) {
            $query->andWhere(['BETWEEN', 'connect_time', $dtFrom->format(DateTimeZoneHelper::DATETIME_FORMAT), $dtTo->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        } else {
            $query->andWhere(['>=', 'connect_time', $dtFrom->format(DateTimeZoneHelper::DATETIME_FORMAT)]);
        }

        if ($region == 99) {
            $query->andWhere(
                ['between', 'dst_number', 74950000000, 74999000000]
            );
        }

        return $query;
    }

    /**
     * Вернуть список статусов
     *
     * @param bool $isWithEmpty
     * @return array
     */
    public function getStatusList($isWithEmpty = false)
    {
        $list = \app\models\Number::$statusList;

        if ($isWithEmpty) {
            $list = ['' => '----'] + $list;
        }

        return $list;
    }

}
