<?php

namespace app\modules\nnp\commands;

use app\modules\nnp\classes\CityLinker;
use app\modules\nnp\classes\OperatorLinker;
use app\modules\nnp\classes\RefreshPrefix;
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
     * Импортировать Россию из Россвязи. 2 минуты.
     *
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \app\exceptions\ModelValidationException
     */
    public function actionRus()
    {
        (new ImportServiceRossvyaz([
            'countryCode' => Country::RUSSIA,
            'delimiter' => ';',
        ]))
            ->run();

        $this->actionLink();
        $this->actionPrefix();
    }

    /**
     * Привязать операторы-регионы-города
     *
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \app\exceptions\ModelValidationException
     */
    public function actionLink()
    {
        echo 'Операторы: ' . OperatorLinker::me()->run() . PHP_EOL;
        echo 'Регионы: ' . RegionLinker::me()->run() . PHP_EOL;
        echo 'Города: ' . CityLinker::me()->run() . PHP_EOL;
    }

    /**
     * Актуализировать префиксы (диапазоны)
     *
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \app\exceptions\ModelValidationException
     */
    public function actionPrefix()
    {
        echo 'Префиксы (диапазоны): ' . RefreshPrefix::me()->refreshByRange() . PHP_EOL;
    }

    /**
     * Актуализировать префиксы (фильтры)
     *
     * @throws \yii\db\Exception
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function actionPrefixFilter()
    {
        echo 'Префиксы (фильтры): ' . RefreshPrefix::me()->refreshByFilter() . PHP_EOL;
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
