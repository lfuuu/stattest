<?php

namespace app\modules\payments;

use app\classes\helpers\ArrayHelper;
use Yii;

class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\payments\controllers';

    /**
     * Init
     */
    public function init()
    {
        parent::init();

        // подключить конфиги
        $params = require __DIR__ . '/config/params.php';

        $localConfigFileName = __DIR__ . '/config/params.local.php';
        if (file_exists($localConfigFileName)) {
            $params = ArrayHelper::merge($params, require $localConfigFileName);
        }

        Yii::configure($this, $params);
    }
}
