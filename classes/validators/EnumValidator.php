<?php
namespace app\classes\validators;

use Yii;
use yii\validators\Validator;

class EnumValidator extends Validator
{
    public $enum;

    public function init()
    {
        parent::init();

        if ($this->message === null) {
          $this->message = Yii::t('yii', '{attribute} is invalid. Must be in range ({range})');
        }
    }

    public function validateValue($value)
    {
        $enumClass = $this->enum;
        if (!$enumClass::hasKey($value)) {
            return [$this->message, ['range' => implode(',', $enumClass::getKeys())]];
        }
        return null;
    }
}