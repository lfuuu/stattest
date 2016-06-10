<?php

namespace app\modules\nnp\forms\numberRange;

use app\modules\nnp\models\NumberRange;

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
     * @return NumberRange
     */
    public function getNumberRangeModel()
    {
        return NumberRange::findOne($this->id);
    }
}