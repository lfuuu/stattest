<?php

namespace app\classes;

use Yii;

class Smarty
{
    private static $smarty = null;

    public static function init()
    {
        if (self::$smarty == null)
        {
            $smarty = new \Smarty;
            $smarty->compile_dir = Yii::$app->params['SMARTY_COMPILE_DIR'];
            $smarty->template_dir = Yii::$app->params['SMARTY_TEMPLATE_DIR'];
            $smarty->registerPlugin("modifier", "mdate", [new \app\classes\DateFunction, "mdate"]);
            self::$smarty = $smarty;
        }

        return self::$smarty;
    }
}
