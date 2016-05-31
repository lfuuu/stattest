<?php

namespace app\modules\nnp\forms;

use app\modules\nnp\models\Destination;

class DestinationFormEdit extends DestinationForm
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
     * @return Destination
     */
    public function getDestinationModel()
    {
        return Destination::findOne($this->id);
    }
}