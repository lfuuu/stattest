<?php

namespace app\classes\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\LogClient;
use app\models\LogClientFields;
use app\classes\Assert;


class LogClientNameChange extends Behavior
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

        if ($event->changedAttributes && isset($event->changedAttributes["name"]))
        {
            $log = new LogClient();

            $log->user_id = \Yii::$app->user->getIdentity()->id;
            $log->ts = (new \DateTime())->format(\DateTime::ATOM);
            $log->client_id = 0;
            $log->super_id = 0;

            $log->{$this->field} = $event->sender->id;

            $log->type = "company_name";

            $company = array(
                "company" => array("from" => $event->changedAttributes["name"], "to" => $event->sender->name)
            );

            $log->comment = serialize($company);
            $log->save();
        }
    }
}
