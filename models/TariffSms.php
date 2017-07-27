<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 * @property
 */
class TariffSms extends ActiveRecord
{
    public static function tableName()
    {
        return 'tarifs_sms';
    }
}