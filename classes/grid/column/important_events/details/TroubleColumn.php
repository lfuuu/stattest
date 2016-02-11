<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use app\classes\Html;
use app\models\Trouble;
use app\models\TroubleState;
use app\models\User;

abstract class TroubleColumn
{

    use DetailsTrait;

    public static function renderCreatedTroubleDetails($column)
    {
        $result = [];
        $properties = ArrayHelper::map($column->properties, 'property', 'value');

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
            ($value = self::renderClientAccount($column->client_id)) !== false
        ) {
            $result[] = $value;
        }

        if (
            isset($properties['user_id'])
            &&
            ($value = self::renderUser($properties['user_id'])) !== false
        ) {
            $result[] = $value;
        }

        return $result;
    }

    public static function renderClosedTroubleDetails($column)
    {
        return self::renderSetStateTroubleDetails($column);
    }

    public static function renderNewCommentTroubleDetails($column)
    {
        $result = self::renderCreatedTroubleDetails($column);
        $properties = ArrayHelper::map($column->properties, 'property', 'value');

        if (
            isset($properties['stage_id'])
            &&
            ($stage = TroubleState::findOne($properties['stage_id'])) !== false
        ) {
            $result[] =
                Html::tag('b', 'Комментарий') .
                $stage->comment;
        }

        return $result;
    }

    public static function renderSetStateTroubleDetails($column)
    {
        $result = self::renderCreatedTroubleDetails($column);
        $properties = ArrayHelper::map($column->properties, 'property', 'value');

        if (
            isset($properties['stage_id'])
            &&
            ($stage = TroubleState::findOne($properties['stage_id'])) !== false
        ) {
            $result[] =
                Html::tag('b', 'Статус') .
                $stage->state->name;
        }

        return $result;
    }

    public static function renderSetResponsibleTroubleDetails($column)
    {
        $result = self::renderCreatedTroubleDetails($column);
        $properties = ArrayHelper::map($column->properties, 'property', 'value');

        if (
            isset($properties['stage_id'])
            &&
            ($stage = TroubleState::findOne($properties['stage_id'])) !== false
        ) {
            $user = User::findOne(['user' => $stage->user_main]);

            $result[] =
                Html::tag('b', 'Ответственный') .
                $user->name;
        }

        return $result;
    }

    private static function renderTrouble($troubleId)
    {
        $trouble = Trouble::findOne($troubleId);

        if ($trouble === null) {
            return false;
        }

        return
            Html::tag('b', 'Зяавка: ') .
            Html::a(
                $trouble->problem,
                Url::toRoute(['/', 'module' => 'tt', 'action' => 'view', 'id' => $trouble->id]),
                ['target' => '_blank']
            );
    }

}