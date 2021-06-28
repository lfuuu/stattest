<?php

namespace app\forms\tariff;

use app\models\DidGroup;
use app\models\DidGroupPriceLevel;

class DidGroupFormEdit extends DidGroupForm
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
     * @return DidGroup
     */
    public function getDidGroupModel()
    {
        return DidGroup::findOne($this->id);
    }

    /**
     * @return DidGroupPriceLevel[]
     */
    public function getDidGroupPriceLevels()
    {
        return DidGroupPriceLevel::findAll(['did_group_id' => $this->id]);
    }
}
