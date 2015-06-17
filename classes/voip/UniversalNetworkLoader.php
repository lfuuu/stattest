<?php

namespace app\classes\voip;

use Yii;

class UniversalNetworkLoader extends BaseNetworkLoader
{
    public static function getName()
    {
        return 'Универсальный загрузчик Сетей';
    }

    public static function overrideSettings()
    {
        return [];
    }

}
