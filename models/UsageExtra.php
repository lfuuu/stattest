<?php

namespace app\models;

use app\classes\bill\ExtraBiller;
use app\classes\model\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\classes\transfer\ExtraServiceTransfer;
use app\dao\services\ExtraServiceDao;
use app\helpers\usages\UsageExtraHelper;
use app\models\usages\UsageInterface;
use app\queries\UsageQuery;
use DateTime;

/**
 * @property int $id
 * @property TariffExtra $tariff
 * @property UsageExtraHelper $helper
 */
class UsageExtra extends ActiveRecord implements UsageInterface
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
        return 'usage_extra';
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @return ExtraServiceDao
     */
    public static function dao()
    {
        return ExtraServiceDao::me();
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return ExtraBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new ExtraBiller($this, $date, $clientAccount);
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
        return Transaction::SERVICE_EXTRA;
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
     * @param $usage
     * @return ExtraServiceTransfer
     */
    public static function getTransferHelper($usage = null)
    {
        return new ExtraServiceTransfer($usage);
    }

    /**
     * @return UsageExtraHelper
     */
    public function getHelper()
    {
        return new UsageExtraHelper($this);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffExtra::tableName());
    }

}
