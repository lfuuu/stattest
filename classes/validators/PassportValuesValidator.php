<?php
namespace app\classes\validators;

use app\models\ClientContragent;
use yii\validators\Validator;
/**
 * Проверка заполненности серии и номера паспорта
 */
class PassportValuesValidator extends Validator
{
    public $skipOnEmpty = false;

    public function validateAttribute($model, $attribute)
    {
        if (!($model->legal_type == ClientContragent::PERSON_TYPE)) {
            return null;
        }

        if ($attribute == 'passport_number' && $model->passport_serial && !$model->passport_number) {
            $this->addError($model, $attribute, \Yii::t('common', 'Passport number must be completed'));
        } elseif ($attribute == 'passport_serial' && !$model->passport_serial && $model->passport_number) {
            $this->addError($model, $attribute, \Yii::t('common', 'Passport series must be completed'));
        }
        return null;
    }
}