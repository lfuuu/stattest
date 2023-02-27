<?php

namespace app\classes\validators;

use app\models\PaymentApiChannel;
use yii\validators\Validator;

class PaymentApiAccessCheckerValidator extends Validator
{
    public $skipOnEmpty = false;

    public $errorMessage = 'Access not allowed';

    public function validateAttribute($model, $attribute)
    {
        if (
            !$model->access_token
            || !$model->channel
            || !PaymentApiChannel::find()->where([
                'code' => $model->channel,
                'access_token' => $model->access_token,
                'is_active' => 1,
            ])->exists()
        ) {
            $this->addError($model, $attribute, $this->errorMessage);
        }
    }

}