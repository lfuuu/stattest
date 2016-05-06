<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\classes\Html;
use app\models\important_events\ImportantEvents;
use app\models\Trouble;
use app\models\TroubleStage;
use app\models\User;

abstract class TroubleColumn
{

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderCreatedTroubleDetails($column)
    {
        $result = [];
        $properties = ArrayHelper::map((array)$column->properties, 'property', 'value');

        if (
            isset($properties['trouble_id'])
            &&
            ($value = self::renderTrouble($properties['trouble_id'])) !== false
        ) {
            $result[] = $value;
        }

        if (
            $column->client_id
            &&
            ($value = DetailsHelper::renderClientAccount($column->client_id)) !== false
        ) {
            $result[] = $value;
        }

        if (
            isset($properties['user_id'])
            &&
            ($value = DetailsHelper::renderUser($properties['user_id'])) !== false
        ) {
            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderClosedTroubleDetails($column)
    {
        return self::renderSetStateTroubleDetails($column);
    }

    public static function renderNewCommentTroubleDetails($column)
    {
        $result = self::renderCreatedTroubleDetails($column);
        $properties = ArrayHelper::map($column->properties, 'property', 'value');

        $stageId = null;

        if (isset($properties['stage_id'])) {
            $stageId = $properties['stage_id'];
        } else {
            if (isset($properties['trouble_id'])) {
                if (($trouble = Trouble::findOne($properties['trouble_id'])) !== null) {
                    /** @var Trouble $trouble */
                    $stageId = $trouble->cur_stage_id;
                }
            }
        }

        if (!is_null($stageId)) {
            if (($stage = TroubleStage::findOne($stageId)) !== null) {
                /** @var TroubleStage $stage */
                $result[] =
                    Html::tag('b', 'Комментарий: ') .
                    $stage->comment;
            }
        }

        return $result;
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderSetStateTroubleDetails($column)
    {
        $result = self::renderCreatedTroubleDetails($column);
        $properties = ArrayHelper::map((array)$column->properties, 'property', 'value');

        $stageId = null;

        if (isset($properties['stage_id'])) {
            $stageId = $properties['stage_id'];
        } else {
            if (isset($properties['trouble_id'])) {
                if (($trouble = Trouble::findOne($properties['trouble_id'])) !== null) {
                    /** @var Trouble $trouble */
                    $stageId = $trouble->cur_stage_id;
                }
            }
        }

        if (!is_null($stageId)) {
            if (($stage = TroubleStage::findOne($stageId)) !== null) {
                /** @var TroubleStage $stage */
                $result[] =
                    Html::tag('b', 'Статус: ') .
                    $stage->state->name;
            }
        }

        return $result;
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderSetResponsibleTroubleDetails($column)
    {
        $result = self::renderCreatedTroubleDetails($column);
        $properties = ArrayHelper::map((array)$column->properties, 'property', 'value');

        $stageId = null;

        if (isset($properties['stage_id'])) {
            $stageId = $properties['stage_id'];
        } else {
            if (isset($properties['trouble_id'])) {
                if (($trouble = Trouble::findOne($properties['trouble_id'])) !== null) {
                    /** @var Trouble $trouble */
                    $stageId = $trouble->cur_stage_id;
                }
            }
        }

        if (!is_null($stageId)) {
            if (($stage = TroubleStage::findOne($stageId)) !== null) {
                /** @var User $user */
                $user = User::findOne(['user' => $stage->user_main]);

                $result[] =
                    Html::tag('b', 'Ответственный: ') .
                    $user->name;
            }
        }

        return $result;
    }

    /**
     * @param int $troubleId
     * @return bool|string
     */
    private static function renderTrouble($troubleId)
    {
        /** @var Trouble $trouble */
        $trouble = Trouble::findOne($troubleId);

        if ($trouble === null) {
            return false;
        }

        if ($trouble->bill_no) {
            return
                Html::tag('b', 'Счет №' . $trouble->bill_no . ': ') .
                Html::a(
                    $trouble->bill_no,
                    Url::toRoute([
                        '/',
                        'module' => 'newaccounts',
                        'action' => 'bill_view',
                        'bill' => $trouble->bill_no
                    ]),
                    ['target' => '_blank']
                );
        } else {
            return
                Html::tag('b', 'Заявка №' . $troubleId . ': ') .
                Html::a(
                    $trouble->problem,
                    Url::toRoute(['/', 'module' => 'tt', 'action' => 'view', 'id' => $trouble->id]),
                    ['target' => '_blank']
                );
        }
    }

}