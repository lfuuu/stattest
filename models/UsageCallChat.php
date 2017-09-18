<?php

namespace app\models;

use app\classes\bill\CallChatBiller;
use app\classes\model\ActiveRecord;
use app\classes\monitoring\UsagesLostTariffs;
use app\dao\services\CallChatServiceDao;
use app\helpers\usages\UsageCallChatHelper;
use app\models\usages\UsageInterface;
use app\queries\UsageQuery;
use DateTime;

/**
 * @property int $id
 * @property-read TariffCallChat $tariff
 * @property
 */
class UsageCallChat extends ActiveRecord implements UsageInterface
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ActiveDateTime' => \app\classes\behaviors\UsageDateTime::className(),
            'ImportantEvents' => \app\classes\behaviors\important_events\UsageAction::className(),
            'ActualCallChatUsage' => \app\classes\behaviors\ActaulizeCallChat::className()
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'usage_call_chat';
    }

    /**
     * @return array
     */
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

    /**
     * @return CallChatServiceDao
     */
    public static function dao()
    {
        return CallChatServiceDao::me();
    }

    /**
     * @return UsageQuery
     */
    public static function find()
    {
        return new UsageQuery(get_called_class());
    }

    /**
     * @param DateTime $date
     * @param ClientAccount $clientAccount
     * @return CallChatBiller
     */
    public function getBiller(DateTime $date, ClientAccount $clientAccount)
    {
        return new CallChatBiller($this, $date, $clientAccount);
    }

    /**
     * @return TariffCallChat
     */
    public function getTariff()
    {
        return $this->hasOne(TariffCallChat::className(), ['id' => 'tarif_id']);
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return Transaction::SERVICE_CALL_CHAT;
    }

    /**
     * @return ClientAccount
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['client' => 'client']);
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
