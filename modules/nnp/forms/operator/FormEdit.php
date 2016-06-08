<?php

namespace app\modules\nnp\forms\operator;

use app\modules\nnp\models\Operator;

class FormEdit extends Form
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
     * @return Operator
     */
    public function getOperatorModel()
    {
        return Operator::findOne($this->id);
    }
}