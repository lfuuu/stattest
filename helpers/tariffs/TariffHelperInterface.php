<?php

namespace app\helpers\tariffs;

interface TariffHelperInterface
{

    /**
     * Получение названия услуги
     * @return string
     */
    public function getTitle();

    /**
     * Получение URL на редактирование услуги
     *
     * @return string
     */
    public function getEditLink();

}