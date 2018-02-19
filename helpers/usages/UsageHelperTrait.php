<?php

namespace app\helpers\usages;

use app\classes\Html;
use yii\db\ActiveRecord;

trait UsageHelperTrait
{

    /**
     * Получение полей для связи с лицевым счетом
     * Поле в услуге => Поле в лицевом счете
     *
     * @return array
     */
    public function getFieldsForClientAccountLink()
    {
        // Поле в услуге, Поле в лицевом счете
        return ['client', 'client'];
    }

    /**
     * @return bool|string
     */
    public function getTariffDescription()
    {
        $tariff = $this->_usage->getTariff();
        if (!($tariff instanceof ActiveRecord)) {
            return false;
        }

        return Html::a($tariff->helper->title, $tariff->helper->editLink, ['target' => '_blank']);
    }

    /**
     * Дата создания услуги
     *
     * @return string|null
     */
    public function getActivationDt()
    {
        $this->_usage->activation_dt;
    }
}