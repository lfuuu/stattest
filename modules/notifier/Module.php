<?php

namespace app\modules\notifier;

use Yii;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use app\classes\HttpClient;

/**
 * @property \app\modules\notifier\components\Actions $actions
 */
class Module extends \yii\base\Module
{

    /** @var HttpClient */
    protected $client;

    /** @var string */
    public $controllerNamespace = 'app\modules\notifier\controllers';

    /** @var array */
    public $config;

    /** @var \yii\httpclient\Request */
    public $request;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        Yii::configure($this, require __DIR__ . '/config.local.php');

        if (!isset($this->config, $this->config['uri'])) {
            throw new InvalidConfigException('Mailer was not configured');
        }

        $this->setComponents([
            'actions' => [
                'class' => '\app\modules\notifier\components\Actions',
            ],
        ]);

        $this->client = new HttpClient;
        $this->client->requestConfig = [
            'format' => HttpClient::FORMAT_JSON,
        ];
        $this->client->responseConfig = [
            'format' => HttpClient::FORMAT_JSON,
        ];
        $this->client->setTransport(\yii\httpclient\CurlTransport::class);

        $this->request = $this->client
            ->createRequest()
            ->setMethod(isset($this->config['request']['method']) ? $this->config['request']['method'] : 'post');
    }

    /**
     * @param string $requestAction
     * @param array $requestData
     * @return mixed
     * @throws \Exception
     */
    public function send($requestAction, array $requestData = [])
    {
        $request = $this->request
            ->setData($requestData)
            ->setUrl($this->config['uri'] . $requestAction);

        if (isset($this->config['auth'])) {
            $this->client->auth($request, $this->config['auth']);
        }

        /** @var \yii\httpclient\Response $response */
        try {
            $response = $this->client->send($request);
        }
        catch (\Exception $e) {
            throw $e;
        }

        if (!$response->getIsOk()) {
            throw new ErrorException($response->getContent());
        }

        return $response->data;
    }

}
