<?php
namespace app\classes\validators;

use app\models\ClientAccount;
use yii\validators\RequiredValidator;

class AccountIdValidator extends RequiredValidator
{

    public $account = null;

    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if (!$model->hasErrors($attribute)) {
            $account = ClientAccount::findOne($model->$attribute);
            if ($account === null) {
                $this->addError($model, $attribute, "Client account with id {$model->$attribute} not found");
            }
            if ($this->account) {
                $model->{$this->account} = $account;
            }
        }
    }

}