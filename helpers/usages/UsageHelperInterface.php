<?php

namespace app\helpers\usages;

use yii\db\ActiveRecord;

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

    /**
     * Получение услуги с которой был осуществлен перенос
     *
     * @return ActiveRecord
     */
    public function getTransferedFrom();

}