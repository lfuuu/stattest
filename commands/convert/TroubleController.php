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

    public function actionCloseKim()
    {
        $sql = <<<SQL
SELECT t.id FROM `tt_troubles` t
inner join tt_stages s on t.cur_stage_id = s.stage_id
where date_creation < '2020-12-01 00:00:00' 
and /**/ t.id = 597834 and/**/  !is_closed
and trouble_type = 'connect'
and user_author = 'AutoLK'
SQL;

        $troubles = \Yii::$app->db->createCommand($sql)->queryColumn();

        foreach ($troubles as $troubleId) {
            $trouble = Trouble::findOne(['id' => $troubleId]);
            $trouble->addStage(61, '(автозакрытие)', null, 60);
        }

    }
}

