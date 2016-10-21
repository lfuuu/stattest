<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\TariffCallChatDao;

/**
 * @property int $id
 * @property
 */
class TariffCallChat extends ActiveRecord
{

    const CALL_CHAT_TARIFF_STATUS_PUBLIC = 'public';
    const CALL_CHAT_TARIFF_STATUS_SPECIAL = 'special';
    const CALL_CHAT_TARIFF_STATUS_ARCHIVE = 'archive';

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tarifs_call_chat';
    }

    /**
     * @return TariffCallChatDao
     */
    public static function dao()
    {
        return TariffCallChatDao::me();
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

}