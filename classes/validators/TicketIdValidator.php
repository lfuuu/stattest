<?php
namespace app\classes\validators;

use app\models\support\Ticket;
use yii\validators\NumberValidator;

class TicketIdValidator extends NumberValidator
{
    public $integerOnly = true;
    public $ticket = null;

    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if (!$model->hasErrors($attribute)) {
            $ticket = Ticket::findOne($model->$attribute);
            if ($ticket === null) {
                $this->addError($model, $attribute, "Ticket with id {$model->$attribute} not found");
            }
            if ($this->ticket) {
                $model->{$this->ticket} = $ticket;
            }
        }
    }
}