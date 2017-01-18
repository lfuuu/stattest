<?php

namespace app\modules\glpi;

use Yii;

/**
 * Работа с GLPI (см. readme)
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\glpi\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\glpi\commands';
        }

        Yii::configure($this, require __DIR__ . '/config/params.php');

        $localConfigFileName = __DIR__ . '/config/params.local.php';
        if (file_exists($localConfigFileName)) {
            Yii::configure($this, require $localConfigFileName);
        }
    }

}
