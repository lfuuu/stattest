<?php
namespace app\models;

use app\classes\validators\InnValidator;
use app\helpers\DateTimeZoneHelper;
use yii\db\ActiveRecord;

class ClientInn extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_inn';
    }

    public function rules()
    {
        return [
            [['client_id', 'inn'], 'required'],
            [['client_id', 'user_id', 'is_active'], 'integer'],
            [['inn', 'comment'], 'string'],
            ['inn', InnValidator::className()],

            ['user_id', 'default', 'value' => \Yii::$app->user->id],
            ['ts', 'default', 'value' => date(DateTimeZoneHelper::DATETIME_FORMAT)],
            ['is_active', 'default', 'value' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'inn' => 'ИНН',
            'comment' => 'Комментарий',
            'user_id' => 'Кто',
            'ts' => 'Когда'
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
