<?php

namespace app\modules\nnp\forms\land;

use app\modules\nnp\models\Land;

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
     * @return Land
     */
    public function getLandModel()
    {
        return Land::findOne($this->id);
    }
}