<?php

namespace app\classes\dictionary\forms;

use app\models\Region;

class RegionFormEdit extends RegionForm
{
    /**
     * Конструктор
     *
     * @throws \InvalidArgumentException
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