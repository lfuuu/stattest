<?php

namespace app\modules\nnp2\forms\numberRange;

use app\modules\nnp2\models\NumberRange;

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
     * @return NumberRange
     */
    public function getNumberRangeModel()
    {
        return NumberRange::findOne($this->id);
    }
}