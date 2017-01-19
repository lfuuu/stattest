<?php

namespace app\modules\webhook;

use Yii;

/**
 * Работа с Webhook
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\webhook\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\webhook\commands';
        }

        Yii::configure($this, require __DIR__ . '/config/params.php');

        $localConfigFileName = __DIR__ . '/config/params.local.php';
        if (file_exists($localConfigFileName)) {
            Yii::configure($this, require $localConfigFileName);
        }
    }

}
