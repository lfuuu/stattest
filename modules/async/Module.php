<?php

namespace app\modules\async;

use app\models\EventQueue;
use app\modules\async\classes\AsyncAdapter;
use app\modules\uu\classes\Dao;
use Yii;
use yii\helpers\ArrayHelper;

class Module extends \yii\base\Module
{
    const EVENT_ASYNC_ADD_ACCOUNT_TARIFF= 'event_async_add_account_tariff';
    const EVENT_ASYNC_PUBLISH_RESULT = 'event_async_publish_result';

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\async\controllers';

    /**
     * Для корректного запуска из консоли
     */
    public function init()
    {
        parent::init();
        if (Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'app\modules\async\commands';
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
     * Добавить УУ-услугу
     *
     * @param array $post
     * @throws \yii\base\InvalidConfigException
     */
    public static function addAccountTariff($post, $requestId = null)
    {
        try {
            $result = ['status' => 'OK', 'result' => Dao::me()->addAccountTariff($post)];
        } catch (\Exception $e) {
            $result = ['status' => 'ERROR', 'code' => $e->getCode(), 'result' => $e->getMessage()];
        }

        EventQueue::go(self::EVENT_ASYNC_PUBLISH_RESULT,
            ['request_id' => $requestId]
            + $result
            + (isset($post['webhook_url']) && $post['webhook_url'] ? ['webhook_url' => $post['webhook_url']] : [])
        );
    }

    /**
     * Отправка результата в очередь
     *
     * @param $data
     */
    public static function publishResult($data)
    {
        AsyncAdapter::me()->publishMessage($data);
    }
}
