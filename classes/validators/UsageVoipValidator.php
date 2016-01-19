<?php
namespace app\classes\validators;

use app\models\UsageVoip;
use yii\validators\RequiredValidator;

class UsageVoipValidator extends RequiredValidator
{

    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if (!$model->hasErrors($attribute)) {
            $usage = UsageVoip::find()->where(['E164' => $model->$attribute])->one();

            if ($usage === null) {
                $this->addError($model, $attribute, 'UsageVoip with ID#' . $model->$attribute . ' not found');
            }
        }
    }

}