<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 */
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