<?php

namespace app\modules\nnp\forms\ndcType;

use app\modules\nnp\models\NdcType;

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
     * @return NdcType
     */
    public function getNdcTypeModel()
    {
        return NdcType::findOne($this->id);
    }
}