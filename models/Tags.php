<?php

namespace app\models;

use yii\db\ActiveRecord;

class Tags extends ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'tags';
    }

}