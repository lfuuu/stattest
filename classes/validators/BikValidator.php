<?php
namespace app\classes\validators;

use yii\validators\Validator;
use app\models\Bik;

class BikValidator extends Validator
{

    public function init()
    {
        parent::init();
        $this->message = 'Указанный БИК не найден';
    }

    public function validateValue($value)
    {
        $value = trim($value);
        if (!($bik = Bik::findOne(['bik' => $value])) instanceof Bik) {
            return [$this->message];
        }
        return null;
    }
}