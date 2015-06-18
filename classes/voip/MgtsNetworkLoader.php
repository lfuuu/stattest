<?php

namespace app\classes\voip;

use Yii;

class MgtsNetworkLoader extends BaseNetworkLoader
{
    public static function getName()
    {
        return 'Сети МГТС';
    }

    public static function overrideSettings()
    {
        return [];
    }

}
