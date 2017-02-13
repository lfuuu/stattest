<?php

namespace app\classes\traits;

trait PgsqlArrayFieldParseTrait
{

    /**
     * @param string $value
     * @return array|bool
     */
    private function _parseFieldValue($value)
    {
        if (empty($value)) {
            return false;
        }

        $items = substr($value, 1, strlen($value) - 2);

        if (empty($items)) {
            return false;
        }

        $items = explode(',', $items);
        $items = array_filter($items, function ($row) {
            return $row !== 'NULL';
        });

        return $items;
    }

}