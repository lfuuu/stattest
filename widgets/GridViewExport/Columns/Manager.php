<?php

namespace app\widgets\GridViewExport\Columns;

use app\classes\Singleton;
use app\models\filter\CallsRawFilter;
use app\modules\uu\filter\AccountTariffFilter;
use app\widgets\GridViewExport\Columns\Config\AccountTariffConfig;
use app\widgets\GridViewExport\Columns\Config\CallsRawConfig;

class Manager extends Singleton
{
    protected static $classMap = [
        AccountTariffFilter::class => AccountTariffConfig::class,
        CallsRawFilter::class => CallsRawConfig::class,
    ];

    protected static $settings = [];

    /**
     * @param string $className
     * @param array $columns
     * @return Settings
     */
    public function getSettings($className, array $columns)
    {
        if (!isset(self::$settings[$className])) {
            $set = Settings::create($columns, []);
            if (isset(self::$classMap[$className])) {
                $configClass = self::$classMap[$className];

                /** @var Config $config */
                $config = $configClass::create();
                $config->updateSettings($set);
            }
            self::$settings[$className] = $set;
        }

        return self::$settings[$className];
    }
}