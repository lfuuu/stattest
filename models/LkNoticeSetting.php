<?php
namespace app\models;

use yii\db\ActiveRecord;

class LkNoticeSetting extends ActiveRecord
{
    public static function tableName()
    {
        return 'lk_notice_settings';
    }
}
