<?php

namespace app\widgets\GridViewExport\Columns;

class Settings
{
    public $columns = [];
    public $eagerFields = [];

    /**
     * @param array $columns
     * @param array $eagerFields
     * @return Settings
     */
    public static function create(array $columns, array $eagerFields)
    {
        $instance = new static();

        $instance->columns = $columns;
        $instance->eagerFields = $eagerFields;

        return $instance;
    }
}