<?php

namespace app\modules\nnp\forms;

use app\modules\nnp\models\Region;

class RegionFormEdit extends RegionForm
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
     * @return Region
     */
    public function getRegionModel()
    {
        return Region::findOne($this->id);
    }
}