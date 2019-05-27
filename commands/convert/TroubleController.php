<?php

namespace app\commands\convert;

use app\dao\TroubleDao;
use app\exceptions\ModelValidationException;
use app\models\Trouble;
use app\models\TroubleStage;
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

    /**
     * Вернуть предыдущий комментарий вместо комментария (автоперевод)
     * @throws ModelValidationException
     */
    public function actionRollBackComment()
    {
        $troublesQuery = Trouble::find()
            ->joinWith('stage')
            ->where(['state_id' => 1]);
        foreach ($troublesQuery->each() as $trouble) {
            /** @var $trouble Trouble */
            $stagesQuery = $trouble->getStages()->orderBy(['stage_id' => SORT_DESC]);
            $neededStage = null;
            foreach ($stagesQuery->each() as $stage) {
                /** @var $stage TroubleStage */
                if ($stage->comment == '(автоперевод)') {
                    $neededStage = $stage;
                } elseif ($neededStage) {
                    /** @var $neededStage TroubleStage **/
                    $neededStage->comment = $stage->comment;
                    if (!$neededStage->save()) {
                        throw new ModelValidationException($neededStage);
                    }
                    break;
                }
            }
        }
    }
}

