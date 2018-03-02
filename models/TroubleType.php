<?php

namespace app\models;

use app\classes\model\ActiveRecord;

/**
 * Class TroubleType
 *
 * @property integer $pk
 * @property string $code
 * @property string $name
 * @property integer $folders
 * @property integer $states
 */
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

