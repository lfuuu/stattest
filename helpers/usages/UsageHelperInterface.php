<?php

namespace app\helpers\usages;

use app\models\usages\UsageInterface;
use yii\db\ActiveRecord;

/**
 * @property-read string $title
 * @property-read string $description
 * @property-read string $help
 * @property-read string $value
 * @property-read mixed $extendsData
 * @property-read string $editLink
 * @property-read ActiveRecord $transferedFrom
 * @property-read $fieldsForClientAccountLink
 * @property-read string|null $tariffDescription
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
     * Получение ключевого значения услуги
     *
     * @return string
     */
    public function getValue();

    /**
     * Получение специфичных данных услуги
     *
     * @return mixed
     */
    public function getExtendsData();

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

    /**
     * Получение информации о тарифном плане услуги
     *
     * @return string|null
     */
    public function getTariffDescription();

}