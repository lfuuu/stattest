<?php

namespace app\modules\nnp\forms\package;

use app\modules\nnp\models\Package;

class FormEdit extends Form
{
    /**
     * конструктор
     */
    public function init()
    {
        if ($this->tariff_id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter tariff_id'));
        }

        parent::init();
    }

    /**
     * @return Package
     */
    public function getPackageModel()
    {
        return Package::findOne($this->tariff_id);
    }
}