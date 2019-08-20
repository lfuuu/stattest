<?php
namespace app\classes\validators;

use app\models\ClientContragent;
use app\models\ClientContragentPerson;
use yii\validators\Validator;

/**
 * Проверка уникальности серии и номера паспорта
 */
class PassportNumberUniqValidator extends Validator
{
    public $skipOnError = true;

    public function validateAttribute($model, $attribute)
    {
        if (!($model->legal_type == ClientContragent::PERSON_TYPE)) {
            return null;
        }

        if (!($model->passport_serial && $model->passport_number)) {
            return null;
        }

        $recordValues = ClientContragentPerson::find()
            ->where(['passport_serial' => $model->passport_serial, 'passport_number' => $model->passport_number])
            ->one();
        if ($recordValues['contragent_id'] == $model->id) {
            return null;
        }

        $isPassportSerialExists = ClientContragentPerson::find()
            ->where(['passport_serial' => $model->passport_serial, 'passport_number' => $model->passport_number])
            ->exists();

        if ($isPassportSerialExists) {
            $this->addError($model, $attribute, \Yii::t('common', 'Passport with such number and series already exists'));
        }
        return null;
    }
}