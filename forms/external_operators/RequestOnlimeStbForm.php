<?php

namespace app\forms\external_operators;

use Yii;
use yii\helpers\ArrayHelper;

class RequestOnlimeStbForm extends RequestOnlimeForm
{

    public function rules()
    {
        return [
            [['fullname', 'address', 'phone', 'operator_name'], 'required'],
            [['fullname', 'address', 'phone', 'comment', 'operator_name'], 'string'],
            ['time_interval', 'in', 'range' => array_keys(self::getTimeIntervals())],
            ['products', 'required', 'message' => 'Выберите хотя бы один товар'],
            ['products_counts', 'required', 'message' => 'Выберите хотя бы один товар'],
        ];
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), ['operator_name' => 'ФИО оператора']);
    }

}