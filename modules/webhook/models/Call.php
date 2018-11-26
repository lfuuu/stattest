<?php

namespace app\modules\webhook\models;

use app\classes\behaviors\CreatedAt;
use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * Class Call
 *
 * @property int $id
 * @property int $abon
 * @property int $calling_number
 * @property string $call_start
 */
class Call extends ActiveRecord
{
    /**
     * Название таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'webhook_call';
    }


    /**
     * Поведение модели
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'createdAt' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'call_start',
                'updatedAtAttribute' => null,
                'value' => new Expression('UTC_TIMESTAMP()'),
            ]
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['abon', 'calling_number'], 'integer'],
            [['abon', 'calling_number'], 'required'],
        ];
    }

    public static function clean()
    {
        return self::deleteAll(['<', 'call_start', (new Expression('UTC_TIMESTAMP() - interval 2 minute'))]);
    }
}