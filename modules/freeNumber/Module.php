<?php

namespace app\modules\freeNumber;

use app\modules\freeNumber\classes\FreeNumberAdapter;
use Yii;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{
    const EVENT_EXPORT_FREE = 'export_free_number_free';
    const EVENT_EXPORT_BUSY = 'export_free_number_busy';

    const ACTION_FREE = 'free';
    const ACTION_BUSY = 'busy';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\freeNumber\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\freeNumber\commands';
        }

        // подключить конфиги
        $params = require __DIR__ . '/config/params.php';

        $localConfigFileName = __DIR__ . '/config/params.local.php';
        if (file_exists($localConfigFileName)) {
            $params = ArrayHelper::merge($params, require $localConfigFileName);
        }

        Yii::configure($this, $params);
    }

    /**
     * Номер стал свободным
     *
     * @param int $number
     * @throws \yii\base\InvalidConfigException
     */
    public static function addFree($number)
    {
        FreeNumberAdapter::me()->publishMessage([
            'number' => $number,
            'action' => self::ACTION_FREE,
        ]);
    }

    /**
     * Номер стал несвободным
     *
     * @param int $number
     * @throws \yii\base\InvalidConfigException
     */
    public static function addBusy($number)
    {
        FreeNumberAdapter::me()->publishMessage([
            'number' => $number,
            'action' => self::ACTION_BUSY,
        ]);
    }
}
