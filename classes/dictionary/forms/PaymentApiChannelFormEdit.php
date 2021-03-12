<?php

namespace app\classes\dictionary\forms;

use app\models\Payment;
use app\models\PaymentApiChannel;

class PaymentApiChannelFormEdit extends PaymentApiChannelForm
{
    /**
     * Конструктор
     *
     * @throws \InvalidArgumentException
     */
    public function init()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter id'));
        }

        parent::init();
    }

    /**
     * @return PaymentApiChannel
     */
    public function getFormModel()
    {
        $model = PaymentApiChannel::find()->where(['id' => $this->id])->one();

        if ($model) {
            $this->isCodeUsed = $model->isUsedCode();
        }

        return $model;
    }
}