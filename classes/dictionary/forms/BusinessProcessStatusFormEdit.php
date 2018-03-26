<?php

namespace app\classes\dictionary\forms;

use app\models\BusinessProcessStatus;

class BusinessProcessStatusFormEdit extends BusinessProcessStatusForm
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
     * @return BusinessProcessStatus
     */
    public function getStatusModel()
    {
        return BusinessProcessStatus::findOne(['id' => $this->id]);
    }
}