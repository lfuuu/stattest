<?php
namespace app\classes\validators;

use app\models\UsageVoip;
use yii\validators\NumberValidator;

class UsageVoipValidator extends NumberValidator
{

    public $skipOnEmpty = false;
    public $account_id_field = null;

    public function validateAttribute($model, $attribute)
    {
        parent::validateAttribute($model, $attribute);

        if ($this->isEmpty($model->$attribute)) {
            $this->addError($model, $attribute, 'UsageVoipID required');
        }

        if (!$model->hasErrors()) {
            $usage = UsageVoip::find()->where(['E164' => $model->$attribute]);

            if ($this->account_id_field) {
                $usage->andWhere(['client' => 'id' . $model->{$this->account_id_field}]);
            }

            if ($usage->one() === null) {
                $this->addError($model, $attribute, 'UsageVoip with ID#' . $model->$attribute . ' not found');
            }
        }
    }

}