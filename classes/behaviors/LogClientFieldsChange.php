<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\LogClient;
use app\models\LogClientFields;
use app\classes\Assert;


class LogClientFieldsChange extends Behavior
{
    public $field = null;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => "afterUpdate"
        ];
    }

    public function setField($field)
    {
        $this->field = $field;
    }

    public function afterUpdate($event)
    {
        Assert::isNotNull($this->field);

        if ($event->changedAttributes)
        {
            $log = new LogClient();

            $log->user_id = \Yii::$app->user->getIdentity()->id;
            $log->ts = (new \DateTime())->format(\DateTime::ATOM);
            $log->client_id = 0;
            $log->super_id = 0;

            $log->{$this->field} = $event->sender->id;

            $log->type="fields";
            $log->comment = implode(",", array_keys($event->changedAttributes));

            $log->save();

            foreach($event->changedAttributes as $field => $value)
            {
                $logField = new LogClientFields();
                
                $logField->ver_id = $log->id;

                $logField->field = $field;
                $logField->value_from = $value;
                $logField->value_to = $event->sender->{$field};

                $logField->save();
            }
        }
    }
}
