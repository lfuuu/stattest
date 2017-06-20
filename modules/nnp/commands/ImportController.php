<?php

namespace app\modules\nnp\commands;

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
     */
    public function actionRus()
    {
        (new ImportServiceRossvyaz)
            ->run(Country::RUSSIA);
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
