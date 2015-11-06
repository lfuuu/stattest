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
     * Результат работы монитора
     * @return ArrayDataProvider
     */
    public function getResult();

}