<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * @property int $id
 */
class TariffExtra extends ActiveRecord
{
    public static function tableName()
    {
        return 'tarifs_extra';
    }

    public function isTest()
    {
        return false;
    }
}