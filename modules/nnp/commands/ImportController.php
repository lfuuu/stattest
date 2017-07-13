<?php

namespace app\modules\nnp\commands;

use app\modules\nnp\classes\CityLinker;
use app\modules\nnp\classes\OperatorLinker;
use app\modules\nnp\classes\RegionLinker;
use app\modules\nnp\media\ImportServiceRossvyaz;
use app\modules\nnp\models\Country;
use app\modules\nnp\models\NumberRange;
use yii\console\Controller;

/**
 * Импорт ННП из справочников
 */
class ImportController extends Controller
{
    /**
     * Импортировать Россию из Россвязи. 2 минуты. Сначала надо disable-trigger, потом enable-trigger
     *
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function actionRus()
    {
        (new ImportServiceRossvyaz(Country::RUSSIA))
            ->run();

        echo 'Операторы: ' . OperatorLinker::me()->run() . PHP_EOL;
        echo 'Регионы: ' . RegionLinker::me()->run() . PHP_EOL;
        echo 'Города: ' . CityLinker::me()->run() . PHP_EOL;
    }

    /**
     * Выключить триггеры
     *
     * @throws \yii\db\Exception
     */
    public function actionDisableTrigger()
    {
        NumberRange::disableTrigger();
    }

    /**
     * Включить триггеры и синхронизировать данные по региональным серверам
     *
     * @throws \yii\db\Exception
     */
    public function actionEnableTrigger()
    {
        NumberRange::enableTrigger();
    }

}
