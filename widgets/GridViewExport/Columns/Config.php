<?php

namespace app\widgets\GridViewExport\Columns;

abstract class Config implements ConfigInterface
{
    const PARAM_NAME_KEYS           = 'keys';
    const PARAM_NAME_EAGER_FIELDS   = 'eager';

    /**
     * @inheritDoc
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Updates export columns settings
     *
     * @param Settings $settings
     * @return Settings
     */
    public function updateSettings($settings)
    {
        $config = $this->getColumnsConfig();

        $exportColumns = [];
        $eagerFields = [];
        foreach ($settings->columns as $column) {
            $fields = [];
            if (isset($column['attribute']) && isset($config[$column['attribute']])) {
                $column = array_merge($column, $config[$column['attribute']][Config::PARAM_NAME_KEYS]);
                $fields =
                    isset($config[$column['attribute']][Config::PARAM_NAME_EAGER_FIELDS]) ?
                        $config[$column['attribute']][Config::PARAM_NAME_EAGER_FIELDS] :
                        [];
                $fields = is_array($fields) ? $fields : array_map('trim', explode(',', $fields));
            }

            $exportColumns[] = $column;
            $eagerFields = array_merge($eagerFields, $fields);
        }

        $settings->columns = $exportColumns;
        $settings->eagerFields = array_unique($eagerFields);

        return $settings;
    }
}