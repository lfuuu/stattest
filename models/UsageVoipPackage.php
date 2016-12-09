<?php
namespace app\models;

use DateTime;
use DateTimeZone;
use app\helpers\DateTimeZoneHelper;
use yii\db\ActiveRecord;
use app\classes\DateTimeWithUserTimezone;
use app\classes\bill\Biller;
use app\classes\bill\VoipPackageBiller;
use app\classes\monitoring\UsagesLostTariffs;
use app\helpers\usages\UsageVoipPackageHelper;
use app\queries\ClientAccountQuery;
use app\models\usages\UsageInterface;
use app\models\billing\StatPackage as BillingStatPackage;
use app\models\billing\Calls as CallsStatPackage;
use app\queries\UsageQuery;
use app\classes\behaviors\important_events\UsageAction;
use app\models\important_events\ImportantEvents;

/**
 * @property int $id
 * @property string client
 * @property string activation_dt
 * @property string expire_dt
 * @property string actual_from
 * @property string actual_to
 * @property int tariff_id
 * @property int usage_voip_id
 * @property int usage_trunk_id
 * @property string status
 *
 * @property Region $connectionPoint
 * @property ClientAccount $clientAccount
 * @property TariffVoipPackage $tariff
 * @property UsageVoip $usageVoip
 * @property UsageVoipPackageHelper $helper
 * @property
 */
class UsageVoipPackage extends ActiveRecord implements UsageInterface
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => UsageAction::className(),
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
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
        return $this->hasOne(TariffVoipPackage::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return UsageVoip
     */
    public function getUsageVoip()
    {
        return $this->hasOne(UsageVoip::className(), ['id' => 'usage_voip_id']);
    }

    /**
     * @return Biller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new VoipPackageBiller($this, $date, $clientAccount);
    }

    /**
     * @param string $dateRangeFrom
     * @param string $dateRangeTo
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getBillingStat($dateRangeFrom = '', $dateRangeTo = '')
    {
        $link = $this->hasMany(BillingStatPackage::className(), ['package_id' => 'id']);

        if ($dateRangeFrom) {
            $dateRangeFromStr =
                (new DateTimeWithUserTimezone($dateRangeFrom,
                    new DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
                    ->modify('first day of this month')
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);

            $link->andWhere(['>=', 'activation_dt', $dateRangeFromStr]);
        }
        if ($dateRangeTo) {
            $dateRangeToStr =
                (new DateTimeWithUserTimezone($dateRangeTo,
                    new DateTimeZone(DateTimeZoneHelper::TIMEZONE_MOSCOW)))
                    ->modify('-1 second')
                    ->setTimezone(new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT))
                    ->modify('last day of this month')
                    ->format(DateTimeZoneHelper::DATETIME_FORMAT);

            $link->andWhere(['<=', 'activation_dt', $dateRangeToStr]);
        }

        return $link->all();
    }

    /**
     * @param string $dateRangeFrom
     * @param string $dateRangeTo
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCallsStat($dateRangeFrom = '', $dateRangeTo = '')
    {
        $link = $this->hasMany(CallsStatPackage::className(), [
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
     * @param UsageInterface $usage
     * @return bool
     */
    public static function getTransferHelper($usage = null)
    {
        return false;
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
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffVoipPackage::tableName(), 'tariff_id');
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

