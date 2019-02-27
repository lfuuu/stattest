<?php

namespace app\widgets\GridViewExport\Columns;

interface ConfigInterface
{
    /**
     * Creates instance
     *
     * @return static
     */
    public static function create();

    /**
     * Returns export columns config
     *
     * @return array
     */
    public function getColumnsConfig();
}