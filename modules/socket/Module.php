<?php

namespace app\modules\socket;

use Yii;

/**
 * Работа с сокетом
 *
 * @link http://socket.io/
 * @link http://elephant.io/
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\socket\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\socket\commands';
        }

        Yii::configure($this, require __DIR__ . '/config/params.php');

        $localConfigFileName = __DIR__ . '/config/params.local.php';
        if (file_exists($localConfigFileName)) {
            Yii::configure($this, require $localConfigFileName);
        }
    }

}
