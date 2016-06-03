<?php

namespace app\classes\voip\forms;

class NumberFormEdit extends NumberForm
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
     * @return \app\models\Number
     */
    public function getNumberModel()
    {
        return \app\models\Number::findOne($this->id);
    }
}