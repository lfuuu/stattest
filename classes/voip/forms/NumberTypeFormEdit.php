<?php

namespace app\classes\voip\forms;

use app\models\NumberType;

class NumberTypeFormEdit extends NumberTypeForm
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
     * @return NumberType
     */
    public function getNumberTypeModel()
    {
        return NumberType::findOne($this->id);
    }
}