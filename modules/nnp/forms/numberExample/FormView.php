<?php

namespace app\modules\nnp\forms\numberExample;

use app\modules\nnp\models\NumberExample;

class FormView extends Form
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
     * @return NumberExample
     */
    public function getNumberExampleModel()
    {
        return NumberExample::findOne(['id' => $this->id]);
    }
}