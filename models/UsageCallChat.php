<?php
namespace app\models;


use DateTime;
use app\queries\UsageQuery;
use yii\db\ActiveRecord;
use app\classes\bill\CallChatBiller;
use app\classes\transfer\CallChatServiceTransfer;
use app\helpers\usages\UsageCallChatHelper;
use app\models\usages\UsageInterface;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\CallChatServiceDao;

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
            'ActualCallChatUsage' => \app\classes\behaviors\ActaulizeCallChat::className()
        ];
    }

    public static function tableName()
    {
        return 'usage_call_chat';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'client' => 'Клиент',
            'actual_from' => 'Дата подключения',
            'actual_to' => 'Дата отключения',
            'tarif_id' => 'Тариф',
            'status' => 'Статус услуги',
            'comment' => 'Коментарий'
        ];
    }

    public static function dao()
    {
        return CallChatServiceDao::me();
    }

    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new CallChatBiller($this, $date, $clientAccount);
    }

    public function getTariff()
    {
        return $this->hasOne(TariffCallChat::className(), ['id' => 'tarif_id']);
    }

    public function getServiceType()
    {
        return Transaction::SERVICE_CALL_CHAT;
    }

    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
    }


    /**
     * @param $usage
     * @return CallChatServiceTransfer
     */

    public static function getTransferHelper($usage)
    {
        //TODO:: realize
        return new CallChatServiceTransfer($usage);
    }

    /**
     * @return UsageCallChatHelper
     */

    public function getHelper()
    {
        return new UsageCallChatHelper($this);
    }

    /**
     * @return array
     */
    public static function getMissingTariffs()
    {
        return UsagesLostTariffs::intoTariffTable(self::className(), TariffCallChat::tableName());
    }

}
