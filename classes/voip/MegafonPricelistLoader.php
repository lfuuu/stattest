<?php

namespace app\classes\voip;

use Yii;

class MegafonPricelistLoader extends BasePricelistLoader
{
    public static function getName()
    {
        return 'Мегафон полный';
    }

    public static function overrideSettings()
    {
        return [
            'full' => 1,
            'compress' => 0,
            'skip_rows' => 1,
            'save_settings' => 1,
            'prefix' => '7',
            'col_1' => 'prefix1',
            'col_2' => 'prefix2_from',
            'col_3' => 'prefix2_to',
            'col_7' => 'rate',
        ];
    }
}
