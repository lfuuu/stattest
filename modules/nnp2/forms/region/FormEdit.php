<?php

namespace app\modules\nnp2\forms\region;

use app\modules\nnp2\models\Region;

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
     * @return Region
     */
    public function getRegionModel()
    {
        return Region::findOne($this->id);
    }
}