<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use app\classes\Html;
use app\models\usages\UsageFactory;
use app\models\LogUsageHistory;

abstract class UsageColumn
{

    use DetailsTrait;

    public static function renderCreatedUsageDetails($column)
    {
        $result = [];
        $properties = ArrayHelper::map($column->properties, 'property', 'value');

        if (
            isset($properties['usage'], $properties['usage_id'])
                &&
            ($value = self::renderUsage($properties['usage'], $properties['usage_id'])) !== false
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

    public static function renderUpdatedUsageDetails($column)
    {
        $result = self::renderCreatedUsageDetails($column);
        $properties = ArrayHelper::map($column->properties, 'property', 'value');

        $fields = LogUsageHistory::findOne(['service_id' => $properties['usage_id']])->fields;

        $changes = '';
        foreach ($fields as $field) {
            $changes .=
                Html::beginTag('tr') .
                    Html::tag('td', $field->field) .
                    Html::tag('td', $field->value_from) .
                    Html::tag('td', $field->value_to) .
                Html::endTag('tr');
        }

        $changes =
            Html::beginTag('div', ['style' => 'float: right; margin-top: 10px; width: 50%;']) .
                Html::beginTag('table', ['width' => '100%', 'class' => 'table table-bordered']) .
                    Html::beginTag('tr') .
                        Html::tag('th', 'Поле').
                        Html::tag('th', 'Значение "До"').
                        Html::tag('th', 'Значение "После"').
                    Html::endTag('tr') .
                    $changes .
                Html::endTag('table') .
            Html::endTag('div');

        array_unshift($result, $changes);

        return $result;
    }

    public static function renderDeletedUsageDetails($column)
    {
        return self::renderCreatedUsageDetails($column);
    }

    public static function renderEnabledUsageDetails($column)
    {
        return self::renderCreatedUsageDetails($column);
    }

    public static function renderDisabledUsageDetails($column)
    {
        return self::renderCreatedUsageDetails($column);
    }

    public static function renderTransferUsageDetails($column)
    {
        $result = [];
        $properties = ArrayHelper::map($column->properties, 'property', 'value');

        if (
            isset($properties['usage'], $properties['usage_id'])
        ) {

            $fromUsage = UsageFactory::getUsage($properties['usage'])->findOne($properties['usage_id']);
            $toUsage = UsageFactory::getUsage($properties['usage'])->findOne($fromUsage['next_usage_id']);

            list($value) = $fromUsage->helper->description;

            $result[] =
                Html::tag('b', 'Услуга: ') . Html::a($value, $toUsage->helper->editLink, ['target' => '_blank']) .
                ' перемещана от ' . Html::a($fromUsage->clientAccount->contragent->name, Url::toRoute(['/client/view', 'id' => $fromUsage->clientAccount->id]), ['target' => '_blank']) .
                ' к ' . Html::a($toUsage->clientAccount->contragent->name, Url::toRoute(['/client/view', 'id' => $toUsage->clientAccount->id]), ['target' => '_blank']);
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

    private static function renderUsage($usage, $usageId)
    {
        $usage = UsageFactory::getUsage($usage)->findOne($usageId);

        if ($usage === null) {
            return 'ID: ' . $usageId;
        }

        list($value) = $usage->helper->description;
        return Html::tag('b', 'Услуга: ') . Html::a($value, $usage->helper->editLink, ['target' => '_blank']);
    }

}