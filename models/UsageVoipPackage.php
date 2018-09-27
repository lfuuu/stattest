<?php

namespace app\models;

use app\classes\behaviors\important_events\UsageAction;
use app\classes\bill\VoipPackageBiller;
use app\classes\DateTimeWithUserTimezone;
use app\classes\model\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\helpers\DateTimeZoneHelper;
use app\helpers\usages\UsageVoipPackageHelper;
use app\models\billing\CallsRaw as CallsStatPackage;
use app\models\billing\StatPackage as BillingStatPackage;
use app\models\important_events\ImportantEvents;
use app\models\usages\UsageInterface;
use app\queries\ClientAccountQuery;
use app\queries\UsageQuery;
use DateTime;
use DateTimeZone;
use yii\db\ActiveQuery;

/**
 * @property int $id
 * @property string $client
 * @property string $activation_dt
 * @property string $expire_dt
 * @property string $actual_from
 * @property string $actual_to
 * @property int $tariff_id
 * @property int $usage_voip_id
 * @property int $usage_trunk_id
 * @property string $status
 *
 * @property-read Region $connectionPoint
 * @property-read ClientAccount $clientAccount
 * @property-read TariffVoipPackage $tariff
 * @property-read UsageVoip $usageVoip
 * @property-read UsageVoipPackageHelper $helper
 * @property-read ActiveQuery $billingStat
 */
class UsageVoipPackage extends ActiveRecord implements UsageInterface
{

    public $billingStat = null;

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => UsageAction::class,
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::class,
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_voip_package';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return TariffVoipPackage
     */
    public function getTariff()
    {
        return $this->hasOne(TariffVoipPackage::class, ['id' => 'tariff_id']);
    }

    /**
     * @return UsageVoip
     */
    public function getUsageVoip()
    {
        return $this->hasOne(UsageVoip::class, ['id' => 'usage_voip_id']);
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return VoipPackageBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VoipPackageBiller($this, $date, $clientAccount);
    }

    /**
     * @param string $dateRangeFrom
     * @param string $dateRangeTo
     * @return array|\app\classes\model\ActiveRecord[]
     */
    public function getBillingStat($dateRangeFrom = '', $dateRangeTo = '')
    {
        $cacheKey = $dateRangeFrom . '#' . $dateRangeTo;

        if (!array_key_exists($cacheKey, $this->billingStat)) {
            $link = $this->hasMany(BillingStatPackage::class, ['package_id' => 'id']);

            if ($dateRangeFrom) {
                $dateRangeFromStr = (new DateTimeWithUserTimezone($dateRangeFrom,
                    new DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
                    ->modify('first day of this month')
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);

                $link->andWhere(['>=', 'activation_dt', $dateRangeFromStr]);
            }

            if ($dateRangeTo) {
                $dateRangeToStr = (new DateTimeWithUserTimezone($dateRangeTo,
                    new DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
                    ->modify('-1 second')
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                    ->modify('last day of this month')
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);

                $link->andWhere(['<=', 'activation_dt', $dateRangeToStr]);
            }

            $this->billingStat[$cacheKey] = $link->all();
        }

        return $this->billingStat[$cacheKey];
    }

    /**
     * @param string $dateRangeFrom
     * @param string $dateRangeTo
     * @return array|\app\classes\model\ActiveRecord[]
     */
    public function getCallsStat($dateRangeFrom = '', $dateRangeTo = '')
    {
        $link = $this->hasMany(CallsStatPackage::class, [
            'service_package_id' => 'id',
            'number_service_id' => 'usage_voip_id',
        ]);

        if ($dateRangeFrom) {
            $link->andWhere([
                '>=',
                'connect_time',
                (new DateTime($dateRangeFrom))->setTime(0, 0, 0)->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ]);
        }

        if ($dateRangeTo) {
            $link->andWhere([
                '<=',
                'connect_time',
                (new DateTime($dateRangeTo))->setTime(23, 59, 59)->format(DateTimeZoneHelper::DATETIME_FORMAT)
            ]);
        }

        return $link->all();
    }

    /**
     * @return UsageVoipPackageHelper
     */
    public function getHelper()
    {
        return new UsageVoipPackageHelper($this);
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_VOIP_PACKAGE;
    }

    /**
     * @return ClientAccountQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['client' => 'client']);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::class, TariffVoipPackage::tableName(), 'tariff_id');
    }

    /**
     * @return null|ImportantEvents
     */
    public function getLastUpdateData()
    {
        return
            ImportantEvents::find()
                ->where(['client_id' => $this->clientAccount->id])
                // Placeholder нельзя брать в кавычки, пляшем с бубном
                ->andWhere('context LIKE "%usage_id__' . $this->id . '%"')
                ->orderBy(['date' => SORT_DESC])
                ->one();
    }

}
