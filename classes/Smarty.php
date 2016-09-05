<?php

namespace app\classes;

use Yii;

class Smarty
{
    private static $smarty = null;

    public static function init()
    {
        if (self::$smarty == null) {
            $smarty = new \Smarty;
            $smarty->setCompileDir(Yii::$app->params['SMARTY_COMPILE_DIR']);
            $smarty->setTemplateDir(Yii::$app->params['SMARTY_TEMPLATE_DIR']);

            $smarty->registerPlugin('modifier', 'mdate', [new \app\classes\DateFunction, 'mdate']);
            $smarty->registerPlugin('modifier', 'wordify', [new \app\classes\Wordifier, 'Make']);

            self::$smarty = $smarty;
        }

        return self::$smarty;
    }
}
