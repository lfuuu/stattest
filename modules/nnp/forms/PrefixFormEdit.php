<?php

namespace app\modules\nnp\forms;

use app\modules\nnp\models\Prefix;

class PrefixFormEdit extends PrefixForm
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
     * @return Prefix
     */
    public function getPrefixModel()
    {
        return Prefix::findOne($this->id);
    }
}