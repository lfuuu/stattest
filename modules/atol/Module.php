<?php

namespace app\modules\atol;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Работа с онлайн-кассой Атол
 *
 * @link https://online.atol.ru/
 */
class Module extends \yii\base\Module
{
    const LOG_CATEGORY = 'atol';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\atol\controllers';

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
