<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\classes\validators\InnValidator;
use app\helpers\DateTimeZoneHelper;

/**
 * Class ClientInn
 *
 * @method static ClientInn findOne($condition)
 */
class ClientInn extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_inn';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_id', 'inn'], 'required'],
            [['client_id', 'user_id', 'is_active'], 'integer'],
            [['inn', 'comment'], 'string'],
            ['inn', InnValidator::class],

            ['user_id', 'default', 'value' => \Yii::$app->user->id],
            ['ts', 'default', 'value' => date(DateTimeZoneHelper::DATETIME_FORMAT)],
            ['is_active', 'default', 'value' => 1],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'inn' => 'ИНН',
            'comment' => 'Комментарий',
            'user_id' => 'Кто',
            'ts' => 'Когда'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
