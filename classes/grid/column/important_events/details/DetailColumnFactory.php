<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\Inflector;

abstract class DetailColumnFactory
{

    private static $columns = [
        UsageColumn::class => [
            'created_usage',
            'updated_usage',
            'deleted_usage',
            'enabled_usage',
            'disabled_usage',
            'transfer_usage',
        ],

        TroubleColumn::class => [
            'created_trouble',
            'closed_trouble',
            'new_comment_trouble',
            'set_state_trouble',
            'set_responsible_trouble',
        ],
    ];

    public static function getColumn($column)
    {
        foreach (self::$columns as $columnClass => $columnRenders) {
            if (in_array($column->event, $columnRenders, true)) {
                $render = 'render' . Inflector::camelize($column->event) . 'Details';

                return $columnClass::$render($column);
            }
        }

        return $column->event;
    }

}