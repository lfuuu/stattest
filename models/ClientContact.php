<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\dao\ClientContactDao;

class ClientContact extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_contacts';
    }

    public function getUserUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public static function dao()
    {
        return ClientContactDao::me();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert))
            return false;

        if ($insert === self::EVENT_BEFORE_INSERT)
            $this->is_active = 1;

        if (!$this->ts)
            $this->ts = date('Y-m-d H:i:s');

        return true;
    }
}
