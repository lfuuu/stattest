<?php

namespace app\modules\nnp2\forms\rangeShort;

use app\modules\nnp2\models\RangeShort;

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
     * @return RangeShort
     */
    public function getRangeShortModel()
    {
        return RangeShort::findOne($this->id);
    }
}