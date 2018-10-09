<?php

namespace app\classes\traits;

trait PgsqlArrayFieldParseTrait
{

    /**
     * Convert PGSQL array field from string to array
     * PGSQL PDO string example: {152,455,46,153,80}
     *
     * @param string $value
     * @return array|bool
     */
    private function _parseFieldValue($value)
    {
        if (empty($value)) {
            return false;
        }

        /** @see \yii\db\pgsql\ColumnSchema::$deserializeArrayColumnToArrayExpression */
        if ($value instanceof \yii\db\ArrayExpression) {
            $items = $value->getValue();
        } else {
            $items = substr($value, 1, -1);
            if (empty($items)) {
                return false;
            }
            $items = explode(',', $items);
        }

        $items = array_filter($items, function ($row) {
            return $row !== 'NULL';
        });

        return $items;
    }
}