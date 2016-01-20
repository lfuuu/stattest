<?php
namespace app\classes\validators;

use app\models\ClientAccount;
use yii\validators\NumberValidator;

class AccountIdValidator extends NumberValidator
{

    public $integerOnly = true;
    public $skipOnEmpty = false;

    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if ($this->isEmpty($model->$attribute)) {
            $this->addError($model, $attribute, 'Client account required');
        }

        if (!$model->hasErrors($attribute)) {
            $account = ClientAccount::findOne($model->$attribute);
            if ($account === null) {
                $this->addError($model, $attribute, 'Client account with id {account_id} not found', ['account_id' => $model->$attribute]);
            }
        }
    }

}