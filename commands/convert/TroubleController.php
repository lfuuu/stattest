<?php

namespace app\commands\convert;

use app\dao\TroubleDao;
use app\models\Trouble;
use Yii;
use yii\console\Controller;


class TroubleController extends Controller
{
    /**
     * Установить состояние "Открыт"
     */
    public function actionSetStateOpen()
    {
        /**
         * Трабл УСПД
         * СПД
         * КоллТр
         * МассТр
         * Отработано
         * Тех поддержка
         * Выдача
         */
        $stateIds = [3, 5, 6, 8, 12, 13, 14];
        $newState = 1; //Открыт
        $troublesQuery = Trouble::find()
            ->joinWith('stage')
            ->where(['in', 'state_id', $stateIds]);
        $userId = Yii::$app->user->identity->getId();

        foreach ($troublesQuery->each() as $trouble) {
            /** @var $trouble Trouble */
            $trouble->addStage($newState, '(автоперевод)', null, $userId);
        }
    }
}

