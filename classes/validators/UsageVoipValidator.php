<?php

namespace app\classes\validators;

use app\modules\uu\models\AccountTariff;
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

        if ($model->hasErrors()) {
            return;
        }

        $usage = UsageVoip::find()->where(['E164' => $model->$attribute]);

        $clientAccount = ClientAccount::findOne($model->{$this->account_id_field});
        $usage->andWhere(['client' => $clientAccount->client]);

        if ($usage->exists()) {
            return null;
        }

        $isAccountTariff = AccountTariff::find()->where([
            'voip_number' => $model->$attribute,
            'client_account_id' => $model->{$this->account_id_field}
        ])->exists();

        if ($isAccountTariff) {
            return;
        }

        $this->addError($model, $attribute, 'UsageVoip with ID#' . $model->$attribute . ' not found');
    }

}