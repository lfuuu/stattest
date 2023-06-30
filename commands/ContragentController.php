<?php

namespace app\commands;

use app\classes\adapters\EventBusContragent;
use app\classes\contragent\importer\lk\CoreLkContragent;
use yii\base\Exception;
use yii\console\Controller;

/**
 * Контрагенты. Конвертации и импорт.
 */

class ContragentController extends Controller
{
    /***
     * Массовое обновление данных контрагентов из ЛК по уже залитым данным
     * @return void
     */
    public function actionUpdateAll()
    {
        CoreLkContragent::update();
    }

    /***
     * Обновление контрагента из ЛК по уже залитым данным
     * @param ?int $contragentId
     * @return void
     */
    public function actionUpdate($contragentId = null)
    {
        if (!$contragentId) {
            throw new \InvalidArgumentException('Не установлен контрагент');
        }

        CoreLkContragent::update($contragentId);
    }

    /***
     * Обновление контрагента в БД
     *
     * @param ?int $contragentId
     * @return void
     */
    public function actionSync($contragentId = null)
    {
        CoreLkContragent::syncDbRow($contragentId);
    }

    /**
     * Слушаем изменения в ЛК. Выбираем только нужное (изменения контрагентов) и применяем к стат-контрагенту
     */
    public function actionListenChanges()
    {
        EventBusContragent::me()->listen();
    }

    /**
     * Синхронизация и обновление одного контрагента.
     *
     * @param int $contragentId
     * @throws Exception
     */
    public function actionSyncAndUpdate($contragentId)
    {
        return CoreLkContragent::syncAndUpdate($contragentId);
    }
}
