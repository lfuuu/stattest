<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\ArrayHelper;
use app\classes\Html;
use app\models\important_events\ImportantEvents;

abstract class NotifyColumn
{

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderZeroBalanceDetails($column)
    {
        return self::renderDetails($column);
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderUnsetZeroBalanceDetails($column)
    {
        return self::renderDetails($column);
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderMinBalanceDetails($column)
    {
        return self::renderDetails($column);
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderUnsetMinBalanceDetails($column)
    {
        return self::renderDetails($column);
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderDayLimitDetails($column)
    {
        return self::renderDetails($column);
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderAddPayNotifDetails($column)
    {
        return self::renderDetails($column);
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    private static function renderDetails($column)
    {
        $result = [];
        $properties = ArrayHelper::map((array) $column->properties, 'property', 'value');

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

        if (isset($properties['balance'])) {
            $result[] = Html::tag('b', 'Баланс: ') . $properties['balance'];
        }

        if (isset($properties['limit'])) {
            $result[] = Html::tag('b', 'Лимит: ') . $properties['limit'];
        }

        if (isset($properties['value'])) {
            $result[] = Html::tag('b', 'Значение на момент события: ') . $properties['value'];
        }

        return $result;
    }

}