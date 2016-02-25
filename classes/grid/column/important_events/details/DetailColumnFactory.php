<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\Inflector;
use app\models\important_events\ImportantEvents;

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

        ClientAccountColumn::class => [
            'new_account',
            'account_changed',
            'extend_account_contract',
            'contract_transfer',
            'account_contract_changed',
            'transfer_contragent',
        ],
    ];

    /**
     * @param ImportantEvents $column
     * @return mixed
     */
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