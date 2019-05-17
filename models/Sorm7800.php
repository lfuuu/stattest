<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * @property string $region_id
 * @property string $number
 * @property string $created_at
 */
class Sorm7800 extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'sorm_7800';
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => null,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

}
