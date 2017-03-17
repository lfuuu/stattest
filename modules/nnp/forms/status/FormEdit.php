<?php

namespace app\modules\nnp\forms\status;

use app\modules\nnp\models\Status;

class FormEdit extends Form
{
    /**
     * Конструктор
     */
    public function init()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter id'));
        }

        parent::init();
    }

    /**
     * @return Status
     */
    public function getStatusModel()
    {
        return Status::findOne($this->id);
    }
}