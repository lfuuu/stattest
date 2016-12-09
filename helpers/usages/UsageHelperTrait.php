<?php

namespace app\helpers\usages;

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

}