<?php

namespace app\widgets\GridViewExport\Columns;

abstract class Config implements ConfigInterface
{
    /**
     * @inheritDoc
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Updates export columns
     *
     * @param array $columns
     * @return array
     */
    public function updateColumns(array $columns)
    {
        $config = $this->getColumnsConfig();
        $exportColumns = [];
        foreach ($columns as $column) {
            if (isset($column['attribute']) && isset($config[$column['attribute']])) {
                $column = array_merge($column, $config[$column['attribute']]);
            }

            $exportColumns[] = $column;
        }

        return $exportColumns;
    }
}