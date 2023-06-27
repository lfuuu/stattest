<?php

namespace app\commands;

use app\classes\adapters\EventBusContragent;
use app\classes\contragent\importer\lk\ContragentLkImporter;
use yii\console\Controller;

/**
 * Контрагенты. Конвертации и импорт.
 */

class ContragentController extends Controller
{
    /***
     * Массовое обновление данных контрагентов из ЛК по уже залитым данным
     * @param ?int $contragentId
     * @return void
     */
    public function actionUpdateAll($contragentId = null)
    {
        (new ContragentLkImporter())->run($contragentId);
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
     * @param int $contragentId
     * @throws \yii\base\Exception
     */
    public function actionSyncAndUpdate($contragentId)
    {
        EventBusContragent::me()->syncContragent($contragentId);
    }
}
