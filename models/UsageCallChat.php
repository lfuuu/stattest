<?php
namespace app\models;

use app\queries\UsageQuery;
use yii\db\ActiveRecord;
use app\classes\transfer\ExtraServiceTransfer;
use app\helpers\usages\UsageExtraHelper;
use app\models\usages\UsageInterface;

/**
 * @property int $id

 * @property TariffCallChat $tariff
 * @property
 */
class UsageCallChat extends ActiveRecord implements UsageInterface
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
        return 'usage_call_chat';
    }

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        //TODO:: realize
        //return new ExtraBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffExtra::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        //TODO:: realize
        //return Transaction::SERVICE_EXTRA;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }


    /**
     * @param $usage
     * @return ExtraServiceTransfer
     */

    public static function getTransferHelper($usage)
    {
        //TODO:: realize
        //return new ExtraServiceTransfer($usage);
    }

    /**
     * @return UsageExtraHelper
     */

    public function getHelper()
    {
        //TODO:: realize
        //return new UsageExtraHelper($this);
    }

    public static function getMissingTariffs()
    {
        //TODO:: realize
        //return UsagesLostTariffs::intoTariffTable(self::className(), TariffExtra::tableName());
    }

}
