<?php

namespace app\classes;

use Yii;

class Smarty
{
    private static $_smarty = null;

    /**
     * @return \Smarty
     */
    public static function init()
    {
        if (self::$_smarty == null) {
            $smarty = new \Smarty;
            $smarty->setCompileDir(Yii::$app->params['SMARTY_COMPILE_DIR']);
            $smarty->setTemplateDir(Yii::$app->params['SMARTY_TEMPLATE_DIR']);

            $smarty->registerPlugin('modifier', 'mdate', [new \app\classes\DateFunction, 'mdate']);
            $smarty->registerPlugin('modifier', 'wordify', [new \app\classes\Wordifier, 'Make']);

            if (defined('WEB_ADDRESS') && defined('WEB_PATH')) {
                $smarty->assign('WEB_PATH', WEB_ADDRESS . WEB_PATH);
            }
            if (defined('WEB_IMAGES_PATH')) {
                $smarty->assign('IMAGES_PATH', WEB_IMAGES_PATH);
            }
            if (defined('WEB_PATH')) {
                $smarty->assign('PATH_TO_ROOT', WEB_PATH);
            }

            self::$_smarty = $smarty;
        }

        return self::$_smarty;
    }
}
