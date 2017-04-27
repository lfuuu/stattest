<?php

namespace app\modules\uu;

use Yii;

/**
 * Универсальные услуги
 *
 * @link http://rd.welltime.ru/confluence/pages/viewpage.action?pageId=4391334
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\uu\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\uu\commands';
        }
    }

}
