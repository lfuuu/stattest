<?php
namespace app\classes\validators;

use app\models\Bill;
use yii\validators\Validator;

class BillNoValidator extends Validator
{
    public $skipOnEmpty = true;

    public $errorMessage = 'Bill not found';

    public function validateAttribute($model, $attribute)
    {
        if (!Bill::find()->where(['bill_no' => $model->$attribute])->exists()) {
            $this->addError($model, $attribute, $this->errorMessage);
        }
    }

}