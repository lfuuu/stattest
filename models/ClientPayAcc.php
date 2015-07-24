<?php
namespace app\models;

use yii\db\ActiveRecord;

class ClientPayAcc extends ActiveRecord
{
    public static function tableName()
    {
        return 'client_pay_acc';
    }

    public function rules()
    {
        return [
            [['client_id', 'pay_acc'], 'required'],
            [['client_id', 'who'], 'integer'],
            [['pay_acc'], 'string'],

            ['who', 'default', 'value' => \Yii::$app->user->id],
            ['date', 'default', 'value' => date('Y-m-d H-i-s')],
        ];
    }

    public function attributeLabels()
    {
        return [
            'pay_acc' => 'Р/С',
            'who' => 'Кто',
            'date' => 'Когда'
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'who']);
    }
}
