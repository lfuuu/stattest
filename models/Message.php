<?php
namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $account_id
 * @property string $subject
 * @property string $create_at
 * @property int $is_read
 *
 * @property MessageText $text
 * @property ClientAccount $account
 */
class Message extends ActiveRecord
{
    public static function tableName()
    {
        return 'message';
    }

    public function getText()
    {
        return $this->hasOne(MessageText::className(), ['message_id' => 'id']);
    }

    public function getAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'account_id']);
    }
}