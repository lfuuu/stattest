<?php
namespace app\classes\validators;

use yii\validators\Validator;

class KppValidator extends Validator
{

    public function init()
    {
        parent::init();
        $this->message = 'Incorrect KPP';
    }

    public function validateValue($value)
    {
        if(strlen($value) !== 9){
            return [$this->message];
        }
        return null;
    }
}