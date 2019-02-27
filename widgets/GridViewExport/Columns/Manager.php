<?php

namespace app\widgets\GridViewExport\Columns;

use app\classes\Singleton;
use app\modules\uu\filter\AccountTariffFilter;
use app\widgets\GridViewExport\Columns\Config\AccountTariffConfig;

class Manager extends Singleton
{
    protected static $classMap = [
        AccountTariffFilter::class => AccountTariffConfig::class,
    ];

    /**
     * Updates columns if needed
     *
     * @param string $className
     * @param array $columns
     * @return array
     */
    public function updateExportColumns($className, array $columns)
    {
        if (isset(self::$classMap[$className])) {
            $configClass = self::$classMap[$className];

            /** @var Config $config */
            $config = $configClass::create();
            $columns = $config->updateColumns($columns);
        }

        return $columns;
    }
}