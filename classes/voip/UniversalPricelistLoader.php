<?php

namespace app\classes\voip;

use Yii;

class UniversalPricelistLoader extends BasePricelistLoader
{
    public static function getName()
    {
        return 'Универсальный загрузчик Цен';
    }

    public static function overrideSettings()
    {
        return [];
    }
}
