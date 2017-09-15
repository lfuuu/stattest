<?php

namespace app\models;

use app\classes\model\ActiveRecord;

class TroubleType extends ActiveRecord
{
    const CONNECT = 8;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tt_types';
    }
}

