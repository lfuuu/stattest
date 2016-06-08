<?php

namespace app\classes\grid\column\important_events\details;

use yii\helpers\Inflector;
use app\models\important_events\ImportantEvents;
use app\models\important_events\ImportantEventsNames;

abstract class DetailColumnFactory
{

    private static $columns = [
        UsageColumn::class => [
            ImportantEventsNames::IMPORTANT_EVENT_CREATED_USAGE,
            ImportantEventsNames::IMPORTANT_EVENT_UPDATED_USAGE,
            ImportantEventsNames::IMPORTANT_EVENT_DELETED_USAGE,
            ImportantEventsNames::IMPORTANT_EVENT_ENABLED_USAGE,
            ImportantEventsNames::IMPORTANT_EVENT_DISABLED_USAGE,
            ImportantEventsNames::IMPORTANT_EVENT_TRANSFER_USAGE,
        ],

        TroubleColumn::class => [
            ImportantEventsNames::IMPORTANT_EVENT_CREATED_TROUBLE,
            ImportantEventsNames::IMPORTANT_EVENT_CLOSED_TROUBLE,
            ImportantEventsNames::IMPORTANT_EVENT_NEW_COMMENT_TROUBLE,
            ImportantEventsNames::IMPORTANT_EVENT_SET_STATE_TROUBLE,
            ImportantEventsNames::IMPORTANT_EVENT_SET_RESPONSIBLE_TROUBLE,
        ],

        ClientAccountColumn::class => [
            ImportantEventsNames::IMPORTANT_EVENT_NEW_ACCOUNT,
            ImportantEventsNames::IMPORTANT_EVENT_ACCOUNT_CHANGED,
            ImportantEventsNames::IMPORTANT_EVENT_EXTEND_ACCOUNT_CONTRACT,
            ImportantEventsNames::IMPORTANT_EVENT_CONTRACT_TRANSFER,
            ImportantEventsNames::IMPORTANT_EVENT_ACCOUNT_CONTRACT_CHANGED,
            ImportantEventsNames::IMPORTANT_EVENT_TRANSFER_CONTRAGENT,
        ],

        NotifyColumn::class => [
            ImportantEventsNames::IMPORTANT_EVENT_ZERO_BALANCE,
            ImportantEventsNames::IMPORTANT_EVENT_UNSET_ZERO_BALANCE,
            ImportantEventsNames::IMPORTANT_EVENT_MIN_BALANCE,
            ImportantEventsNames::IMPORTANT_EVENT_UNSET_MIN_BALANCE,
            ImportantEventsNames::IMPORTANT_EVENT_DAY_LIMIT,
            ImportantEventsNames::IMPORTANT_EVENT_ADD_PAY_NOTIF,

        ],

        PaymentColumn::class => [
            ImportantEventsNames::IMPORTANT_EVENT_PAYMENT_ADD,
            ImportantEventsNames::IMPORTANT_EVENT_PAYMENT_DELETE,
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

        return DefaultColumn::render($column);
    }

}