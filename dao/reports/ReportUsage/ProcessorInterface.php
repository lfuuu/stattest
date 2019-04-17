<?php

namespace app\dao\reports\ReportUsage;

interface ProcessorInterface
{
    /**
     * Processor constructor.
     * @param Config $config
     */
    public function __construct(Config $config);

    /**
     * Пре-обработка
     * @throws \Exception
     */
    public function processBefore();

    /**
     * Получение, разбивка и обработка данных статистики
     *
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function processItems();

    /**
     * Получение данных
     *
     * @return array
     * @throws \yii\db\Exception
     * @throws \Exception
     */
    public function getItems();

    /**
     * Обработчик записи
     *
     * @param array $item
     */
    public function processItem(array $item);

    /**
     * Пост-обработка
     */
    public function processAfter();

    /**
     * Статистика по телефонии
     *
     * @return array
     */
    public function getResult();
}