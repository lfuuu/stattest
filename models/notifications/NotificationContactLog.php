<?php
namespace app\models\notifications;

use yii\db\ActiveRecord;
use app\models\ClientContact;

class NotificationContactLog extends ActiveRecord
{

    public static function tableName()
    {
        return 'notification_contact_log';
    }

    public function getContact()
    {
        return $this->hasOne(ClientContact::className(), ['id' => 'contact_id']);
    }

}
