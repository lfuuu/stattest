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
    public static function tableName()
    {
        return 'tarifs_call_chat';
    }

    public static function dao()
    {
        return TariffCallChatDao::me();
    }
}