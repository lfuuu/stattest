<?php

namespace app\modules\callTracking;

use app\classes\helpers\ArrayHelper;
use app\modules\callTracking\models\AccountTariff;
use Yii;

/**
 * Универсальные услуги
 */
class Module extends \yii\base\Module
{
    const EVENT_EXPORT_ACCOUNT_TARIFF = 'calltracking_at';
    const EVENT_EXPORT_VOIP_NUMBER = 'calltracking_number';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\callTracking\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();

        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\callTracking\commands';
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
     * @return bool
     */
    public static function isAvailable()
    {
        return strpos(AccountTariff::getDb()->username, 'readonly') === false;
    }
}
