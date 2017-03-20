<?php

namespace app\helpers\usages;

use app\models\usages\UsageInterface;
use yii\db\ActiveRecord;

/**
 * Interface UsageHelperInterface
 * @package app\helpers\usages
 * @property string $title
 * @property string $description
 * @property string $help
 * @property string $editLink
 * @property ActiveRecord $transferedFrom
 * @property $fieldsForClientAccountLink
 */
interface UsageHelperInterface
{

    /**
     * Получение названия услуги
     *
     * @return string
     */
    public function getTitle();

    /**
     * Получение описания услуги (название / тариф etc)
     *
     * @return array
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
     * @return UsageInterface
     */
    public function getTransferedFrom();

    /**
     * Получение полей для связи с лицевым счетом
     * Поле в услуге => Поле в лицевом счете
     *
     * @return array
     */
    public function getFieldsForClientAccountLink();

}