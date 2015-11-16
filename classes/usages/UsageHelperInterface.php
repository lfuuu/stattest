<?php

namespace app\classes\usages;

interface UsageHelperInterface
{

    /**
     * Получение названия услуги
     * @return string
     */
    public function getTitle();

    /**
     * Получение описания услуги (название / тариф etc)
     * @return mixed
     */
    public function getDescription();

    /**
     * Получение специфических условий для услуги
     * @return string
     */
    public function getHelp();

    /**
     * Получение URL на редактирование услуги
     *
     * @return string
     */
    public function getEditLink();

}