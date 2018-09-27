<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property int $account_id
 * @property string $subject
 * @property string $create_at
 * @property int $is_read
 *
 * @property-read MessageText $text
 * @property-read ClientAccount $account
 */
class Message extends ActiveRecord
{
    public static function tableName()
    {
        return 'message';
    }

    public function getText()
    {
        return $this->hasOne(MessageText::class, ['message_id' => 'id']);
    }

    public function getAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'account_id']);
    }
}