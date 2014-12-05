<?php
namespace app\classes\validators;

use app\models\mongo\CoreUser;
use yii\validators\StringValidator;

class CoreUserIdValidator extends StringValidator
{
    public $length = 24;
    public $user = null;

    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if (!$model->hasErrors($attribute)) {
            $user = CoreUser::findOne($model->$attribute);
            if ($user === null) {
                $this->addError($model, $attribute, "User with id {$model->$attribute} not found");
            }
            if ($this->user) {
                $model->{$this->user} = $user;
            }
        }
    }
}