<?php
namespace app\models;

use DateTime;
use app\queries\UsageQuery;
use yii\db\ActiveRecord;
use app\classes\bill\WelltimeBiller;
use app\classes\transfer\WelltimeServiceTransfer;
use app\dao\services\WelltimeServiceDao;
use app\classes\monitoring\UsagesLostTariffs;
use app\helpers\usages\UsageWelltimeHelper;
use app\models\usages\UsageInterface;

/**
 * @property int $id
 *
 * @property TariffExtra $tariff
 * @property
 */
class UsageWelltime extends ActiveRecord implements UsageInterface
{

    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
        ];
    }

    public static function tableName()
    {
        return 'usage_welltime';
    }

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public static function dao()
    {
        return WelltimeServiceDao::me();
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new WelltimeBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffExtra::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_WELLTIME;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }

    public function getRegionName()
    {
        return $this->hasOne(Region::className(), ['id' => 'region']);
    }

    /**
     * @param $usage
     * @return WelltimeServiceTransfer
     */
    public static function getTransferHelper($usage)
    {
        return new WelltimeServiceTransfer($usage);
    }

    /**
     * @return UsageWelltimeHelper
     */
    public function getHelper()
    {
        return new UsageWelltimeHelper($this);
    }

    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffExtra::tableName());
    }

}
