<?php

namespace app\models;

use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;

/**
 * Class ClientPayAcc
 *
 * @method static ClientPayAcc findOne($condition)
 */
class ClientPayAcc extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'client_pay_acc';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_id', 'pay_acc'], 'required'],
            [['client_id', 'who'], 'integer'],
            [['pay_acc'], 'string'],

            ['who', 'default', 'value' => \Yii::$app->user->id],
            ['date', 'default', 'value' => date(DateTimeZoneHelper::DATETIME_FORMAT)],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'pay_acc' => 'Р/С',
            'who' => 'Кто',
            'date' => 'Когда'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'who']);
    }
}
