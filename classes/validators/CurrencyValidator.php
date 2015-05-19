<?php
namespace app\classes\validators;

use app\models\Currency;
use Yii;
use yii\validators\Validator;

class CurrencyValidator extends Validator
{
    public function init()
    {
        parent::init();

        if ($this->message === null) {
          $this->message = Yii::t('yii', '{attribute} is invalid. Must be in range ({range})');
        }
    }

    public function validateValue($value)
    {
        $keyValue = Currency::map();
        if (!isset($keyValue[$value])) {
            return [$this->message, ['range' => implode(',', Currency::enum())]];
        }
        return null;
    }
}