<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;

class ImportantEventsRulesConditions extends ActiveRecord
{

    public static $conditions = [
        '==' => 'Равно',
        '!=' => 'Не равно',
        '<=' => 'Меньше или равно',
        '>=' => 'Больше или равно',
        '<' => 'Меньше',
        '>' => 'Больше',
        'isset' => 'Существует',
    ];

    public static function tableName()
    {
        return 'important_events_rules_conditions';
    }

    public function rules()
    {
        return [
            [['property',], 'required'],
            [['value'], 'string'],
            ['condition', 'in', 'range' => array_keys(self::$conditions)],
        ];
    }

    public function attributeLabels()
    {
        return [
            'property' => 'Свойство',
            'condition' => 'Условие',
            'value' => 'Значение',
        ];
    }

}