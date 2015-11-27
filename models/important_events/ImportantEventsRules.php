<?php

namespace app\models\important_events;

use yii\db\ActiveRecord;
use yii\validators\RequiredValidator;
use app\classes\actions\message\SendActionFactory;
use app\models\message\Template as MessageTemplate;

class ImportantEventsRules extends ActiveRecord
{

    public $conditions = [];

    public function afterFind()
    {
        if (count($this->allConditions)) {
            foreach ($this->allConditions as $condition) {
                $this->conditions[] = [
                    'property' => $condition->property,
                    'condition' => $condition->condition,
                    'value' => $condition->value,
                ];
            }
        }
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            ImportantEventsRulesConditions::deleteAll($this->id);
            return true;
        }
        else {
            return false;
        }
    }

    public static function tableName()
    {
        return 'important_events_rules';
    }

    public function rules()
    {
        return [
            [['title', 'action', 'event', 'message_template_id'], 'required'],
            [['message_template_id'], 'integer'],
            ['conditions', 'validateConditions'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'title' => 'Название',
            'action' => 'Действие',
            'event' => 'Событие',
            'message_template_id' => 'Шаблон сообщения',
        ];
    }

    public function validateConditions($attribute)
    {
        $requiredValidator = new RequiredValidator;

        foreach($this->$attribute as $index => $row) {
            $error = null;
            $requiredValidator->validate($row['property'], $error);
            if (!empty($error)) {
                $key = $attribute . '[' . $index . '][property]';
                $this->addError($key, 'Необходимо указать свойство');
            }
        }
    }

    public function getAction()
    {
        return SendActionFactory::me()->get($this->action);
    }

    public function getEventInfo()
    {
        return $this->hasOne(ImportantEvents::className(), ['event' => 'event']);
    }

    public function getTemplate()
    {
        return $this->hasOne(MessageTemplate::className(), ['id' => 'message_template_id']);
    }

    public function getAllConditions()
    {
        return $this->hasMany(ImportantEventsRulesConditions::className(), ['rule_id' => 'id']);
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        parent::save();

        ImportantEventsRulesConditions::deleteAll(['rule_id' => $this->id]);
        foreach ($this->conditions as $condition) {
            $record = new ImportantEventsRulesConditions;
            $record->setAttribute('rule_id', $this->id);
            $record->setAttributes($condition);
            if (!$record->validate()) {
                $this->addError($record->getErrors());
            }
            $record->save();
        }

        return $this->hasErrors() ? false : true;
    }

}