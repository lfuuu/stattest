<?php

namespace app\classes\grid\column\important_events\details;

use app\classes\Html;
use app\models\important_events\ImportantEvents;

abstract class DefaultColumn
{

    /**
     * @param ImportantEvents $column
     * @return array
     */
    public static function render($column)
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

        if (isset($properties['value'])) {
            $result[] = DetailsHelper::renderValue((string)$properties['value']);
        }

        return $result;
    }

}