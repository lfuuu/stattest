<?php

namespace app\forms\tariff;

use app\models\DidGroup;

class DidGroupFormEdit extends DidGroupForm
{
    /**
     * конструктор
     */
    public function init()
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter id'));
        }

        parent::init();
    }

    /**
     * @return DidGroup
     */
    public function getDidGroupModel()
    {
        return DidGroup::findOne($this->id);
    }
}