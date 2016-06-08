<?php

namespace app\classes\grid\column\important_events\details;

use app\classes\Html;
use app\models\Currency;
use app\models\important_events\ImportantEvents;

abstract class PaymentColumn
{

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderPaymentAddDetails($column)
    {
        return self::renderDetails($column);
    }

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function renderPaymentDelDetails($column)
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
        $properties = $column->properties;

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
            ($value = DetailsHelper::renderUser((string)$properties['user_id'])) !== false
        ) {
            $result[] = $value;
        }

        if (isset($properties['balance'])) {
            $result[] = DetailsHelper::renderBalance((string)$properties['balance']);
        }

        if (isset($properties['sum'])) {
            $result[] = Html::tag('b', 'Сумма: ') . (string)$properties['sum'];
        }

        if (isset($properties['currency'])) {
            $result[] = Html::tag('b', 'Валюта: ') . Currency::symbol((string)$properties['currency']);
        }

        return $result;
    }

}