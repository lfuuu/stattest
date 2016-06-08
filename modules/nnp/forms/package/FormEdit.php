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
        if ($this->id === null) {
            throw new \InvalidArgumentException(\Yii::t('tariff', 'You should enter id'));
        }

        parent::init();
    }

    /**
     * @return Package
     */
    public function getPackageModel()
    {
        return Package::findOne($this->id);
    }
}