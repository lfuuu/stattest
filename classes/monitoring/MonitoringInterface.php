<?php

namespace app\classes\monitoring;

use yii\data\ArrayDataProvider;

interface MonitoringInterface
{

    /**
     * Получение ключа монитора (используется при подключении шаблона etc)
     * @return string
     */
    public function getKey();

    /**
     * Получение описания монитора
     * @return string
     */
    public function getTitle();

    /**
     * Получение подробного описания монитора
     * @return string
     */
    public function getDescription();

    /**
     * Получение списка столбцов для отображения результата
     * @return array
     */
    public function getColumns();

    /**
     * Результат работы монитора
     * @return ArrayDataProvider
     */
    public function getResult();

}