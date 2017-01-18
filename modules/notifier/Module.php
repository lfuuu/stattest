<?php

namespace app\modules\notifier;

use app\classes\HttpClient;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

/**
 * @property \app\modules\notifier\components\Actions $actions
 */
class Module extends \yii\base\Module
{

    /** @var string */
    public $controllerNamespace = 'app\modules\notifier\controllers';

    /** @var \yii\httpclient\Request */
    public $request;

    /** @var array */
    public $config;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $localConfigFileName = __DIR__ . '/config.local.php';
        if (file_exists($localConfigFileName)) {
            Yii::configure($this, require $localConfigFileName);
        }

        if (!isset($this->config, $this->config['uri'])) {
            throw new InvalidConfigException('Mailer was not configured');
        }

        $this->setComponents([
            'actions' => [
                'class' => '\app\modules\notifier\components\Actions',
            ],
        ]);

        $this->request = (new HttpClient)
            ->createJsonRequest()
            ->setMethod(isset($this->config['request']['method']) ? $this->config['request']['method'] : 'post')
            ->auth(isset($config['auth']) ? $config['auth'] : []);
    }

    /**
     * @param string $requestAction
     * @param array $requestData
     * @return mixed
     * @throws \Exception
     */
    public function send($requestAction, array $requestData = [])
    {
        try {
            $response = $this->request
                ->setData($requestData)
                ->setUrl($this->config['uri'] . $requestAction)
                ->send();
        } catch (\Exception $e) {
            throw $e;
        }

        if (!$response->getIsOk()) {
            throw new BadRequestHttpException($response->getContent());
        }

        return $response->data;
    }

}
