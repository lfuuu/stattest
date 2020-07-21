<?php

namespace app\modules\nnp2\forms\ndcType;

use app\modules\nnp2\models\NdcType;

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
     * @return NdcType
     */
    public function getNdcTypeModel()
    {
        return NdcType::findOne($this->id);
    }
}