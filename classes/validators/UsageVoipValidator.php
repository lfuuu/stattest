<?php
namespace app\classes\validators;

use yii\validators\NumberValidator;
use app\models\ClientAccount;
use app\models\UsageVoip;

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

            if (!is_null($this->account_id_field)) {
                $clientAccount = ClientAccount::findOne($model->{$this->account_id_field});
                $usage->andWhere(['client' => $clientAccount->client]);
            }

            if ($usage->one() === null) {
                $this->addError($model, $attribute, 'UsageVoip with ID#' . $model->$attribute . ' not found');
            }
        }
    }

}