<?php

namespace app\models;

use app\classes\bill\WelltimeBiller;
use app\classes\model\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\WelltimeServiceDao;
use app\helpers\usages\UsageWelltimeHelper;
use app\models\usages\UsageInterface;
use app\queries\UsageQuery;
use DateTime;

/**
 * @property int $id
 *
 * @property-read TariffExtra $tariff
 * @property-read UsageWelltimeHelper $helper
 */
class UsageWelltime extends ActiveRecord implements UsageInterface
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_welltime';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return WelltimeServiceDao
     */
    public static function dao()
    {
        return WelltimeServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return WelltimeBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new WelltimeBiller($this, $date, $clientAccount);
    }

    /**
     * @return TariffExtra
     */
    public function getTariff()
    {
        return $this->hasOne(TariffExtra::className(), ['id' => 'tarif_id']);
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_WELLTIME;
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    /**
     * @return Region
     */
    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    /**
     * @return UsageWelltimeHelper
     */
    public function getHelper()
    {
        return new UsageWelltimeHelper($this);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffExtra::tableName());
    }

}
