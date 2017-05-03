<?php

namespace app\modules\notifier;

use app\classes\HttpClient;
use app\classes\Navigation;
use app\classes\NavigationBlock;
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

    private $_isTestEnvironment = false;

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (YII_ENV === 'test') {
            $this->_isTestEnvironment = true;
            return $this->_isTestEnvironment;
        }

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
                'module' => $this,
            ],
        ]);

        $this->request = (new HttpClient)
            ->createJsonRequest()
            ->setMethod(isset($this->config['request']['method']) ? $this->config['request']['method'] : 'post')
            ->auth(isset($this->config['auth']) ? $this->config['auth'] : []);

        return true;
    }

    /**
     * @param Navigation $navigation
     */
    public function getNavigation(Navigation $navigation)
    {
        $navigation->addBlock(
            NavigationBlock::create()
                ->setId('notifier')
                ->setTitle('Mailer')
                ->addItem('Общие схемы оповещения', ['/notifier/schemes'])
                ->addItem('Персональная схема оповещения', ['/notifier/personal-scheme'])
                ->addItem('Шаблоны почтовых оповещений', ['/notifier/email-templates'], ['mail.w'])
                ->addItem('Управление оповещениями', ['/notifier/control'])
                ->addItem('Мониторинг расхождения персональных схем', ['/notifier/personal-scheme/monitoring'])
        );
    }

    /**
     * @param string $requestAction
     * @param array $requestData
     * @return mixed
     * @throws \Exception
     */
    public function send($requestAction, array $requestData = [])
    {
        if ($this->_isTestEnvironment) {
            return [];
        }

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
