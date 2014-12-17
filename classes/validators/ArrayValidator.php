<?php
namespace app\classes\validators;

use Yii;
use yii\validators\Validator;

class ArrayValidator extends Validator
{
    public $validator;


    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid. Must be array');
        }
    }

    public function validateValue($value)
    {
        if (is_array($value)) {
            if ($this->validator) {
              $validator = Yii::createObject($this->validator);
              foreach ($value as $item) {
                $result = $validator->validateValue($item);
                if (!empty($result)) {
                  return $result;
                }
              }
            }
            return null;
        } else {
            return [$this->message, []];
        }
    }
}