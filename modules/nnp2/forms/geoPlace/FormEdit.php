<?php

namespace app\modules\nnp2\forms\geoPlace;

use app\modules\nnp2\models\GeoPlace;

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
     * @return GeoPlace
     */
    public function getGeoPlaceModel()
    {
        return GeoPlace::findOne($this->id);
    }
}