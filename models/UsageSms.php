<?php

namespace app\models;

use app\classes\bill\SmsBiller;
use app\classes\model\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\SmsServiceDao;
use app\helpers\usages\UsageSmsHelper;
use app\models\usages\UsageInterface;
use app\queries\UsageQuery;
use DateTime;

/**
 * @property int $id
 * @property-read TariffSms $tariff
 * @property-read UsageSmsHelper $helper
 */
class UsageSms extends ActiveRecord implements UsageInterface
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::class,
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::class,
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_sms';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return SmsServiceDao
     */
    public static function dao()
    {
        return SmsServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return SmsBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new SmsBiller($this, $date, $clientAccount);
    }

    /**
     * @return TariffSms
     */
    public function getTariff()
    {
        return $this->hasOne(TariffSms::class, ['id' => 'tarif_id']);
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_SMS;
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['client' => 'client']);
    }

    /**
     * @return Region
     */
    public function getRegionName()
    {
        return $this->hasOne(Region::class, ['id' => 'region']);
    }

    /**
     * @return UsageSmsHelper
     */
    public function getHelper()
    {
        return new UsageSmsHelper($this);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::class, TariffSms::tableName());
    }

}
