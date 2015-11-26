<?php

namespace app\models;

use yii\db\ActiveRecord;
use app\classes\actions\message\SendActionFactory;
use app\models\message\Template as MessageTemplate;

class ImportantEventsRules extends ActiveRecord
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
        return 'important_events_rules';
    }

    public function rules()
    {
        return [
            [['title', 'action', 'message_template_id', 'property'], 'required'],
            [['message_template_id'], 'integer'],
            ['condition', 'in', 'range' => array_keys(self::$conditions)],
            [['value'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'Название',
            'property' => 'Свойство',
            'condition' => 'Условие',
            'value' => 'Значение',
            'action' => 'Действие',
            'message_template_id' => 'Шаблон сообщения',
        ];
    }

    public function getAction()
    {
        return SendActionFactory::me()->get($this->action);
    }

    public function getTemplate()
    {
        return $this->hasOne(MessageTemplate::className(), ['id' => 'message_template_id']);
    }

}