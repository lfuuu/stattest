<?php
namespace app\models;

use DateTime;
use DateTimeZone;
use yii\db\ActiveRecord;
use app\classes\DateTimeWithUserTimezone;
use app\classes\bill\Biller;
use app\classes\bill\VoipPackageBiller;
use app\classes\transfer\VoipPackageServiceTransfer;
use app\classes\monitoring\UsagesLostTariffs;
use app\helpers\usages\UsageVoipPackageHelper;
use app\queries\ClientAccountQuery;
use app\models\usages\UsageInterface;
use app\models\billing\StatPackage as BillingStatPackage;
use app\models\billing\Calls as CallsStatPackage;
use app\queries\UsageQuery;

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
 * @property
 */
class UsageVoipPackage extends ActiveRecord implements UsageInterface
{

    public function behaviors()
    {
        return [
            'ImportantEvent' => \app\classes\behaviors\important_events\UsageVoipPackage::className(),
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
        ];
    }

    public static function tableName()
    {
        return 'usage_voip_package';
    }

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(TariffVoipPackage::className(), ['id' => 'tariff_id']);
    }

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
                (new DateTimeWithUserTimezone($dateRangeFrom, $this->clientAccount->timezone))
                    ->modify('first day of this month')
                    ->setTimezone(new DateTimeZone(DateTimeWithUserTimezone::TIMEZONE_DEFAULT))
                    ->format(DateTime::ATOM);

            $link->andWhere(['>=', 'activation_dt', $dateRangeFromStr]);
        }
        if ($dateRangeTo) {
            $dateRangeToStr =
                (new DateTimeWithUserTimezone($dateRangeTo, $this->clientAccount->timezone))
                    ->modify('-1 second')
                    ->setTimezone(new DateTimeZone(DateTimeWithUserTimezone::TIMEZONE_DEFAULT))
                    ->modify('last day of this month')
                    ->format(DateTime::ATOM);

            $link->andWhere(['<=', 'activation_dt', $dateRangeToStr]);
        }

        return $link->all();
    }

    /**
     * @param string $dataRangeFrom
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
            $link->andWhere(['>=', 'connect_time', (new DateTime($dateRangeFrom))->setTime(0, 0, 0)->format(DateTime::ATOM)]);
        }
        if ($dateRangeTo) {
            $link->andWhere(['<=', 'connect_time', (new DateTime($dateRangeTo))->setTime(23, 59, 59)->format(DateTime::ATOM)]);
        }

        return $link->all();
    }

    /**
     * @param $usage
     * @return VoipPackageServiceTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new VoipPackageServiceTransfer($usage);
    }

    /**
     * @return UsageVoipPackageHelper
     */
    public function getHelper()
    {
        return new UsageVoipPackageHelper($this);
    }

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

    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffVoipPackage::tableName(), 'tariff_id');
    }

}

