<?php

namespace app\classes;

use Yii;
use yii\base\InvalidCallException;
use yii\httpclient\Response;
use yii\web\BadRequestHttpException;

class HttpRequest extends \yii\httpclient\Request
{
    private $_isCheckOk = true;

    /**
     * Добавить авторизацию
     *
     * @param array $config
     * @return self
     */
    public function auth(array $config)
    {
        if (!isset($config['method'])) {
            return $this;
        }

        switch ($config['method']) {
            case 'basic': {
                $this->addOptions([
                    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                    CURLOPT_USERPWD =>
                        (isset($config['user']) ? $config['user'] : '') .
                        ':' .
                        (isset($config['passwd']) ? $config['passwd'] : ''),
                ]);
                break;
            }

            case 'bearer': {
                $this->addHeaders([
                    'Authorization' => 'Bearer ' . (isset($config['token']) ? $config['token'] : ''),
                ]);
                break;
            }
        }

        return $this;
    }

    /**
     * Выполнить запрос
     *
     * @param string $logCategory
     * @return Response
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function send($logCategory = 'application')
    {
        $debugInfoRequest = 'Request: ' . $this->_getDebugInfo();
        Yii::info($debugInfoRequest, $logCategory);
        $httpClientLogger = HttpClientLogger::me();
        $httpClientLogger->add($debugInfoRequest);

        $response = parent::send();

        $debugInfoResponse = sprintf('Response = %s', print_r($response->data, true)) . PHP_EOL;
        Yii::info($debugInfoRequest . PHP_EOL . PHP_EOL . $debugInfoResponse, $logCategory);
        $httpClientLogger->add($debugInfoResponse);

        if ($this->_isCheckOk && !$response->getIsOk()) {
            throw new BadRequestHttpException($debugInfoRequest . PHP_EOL . PHP_EOL . $debugInfoResponse);
        }

        return $response;
    }

    /**
     * Выполнить запрос, проверить ответ и вернуть полученные данные
     *
     * @return mixed
     * @throws InvalidCallException
     * @throws BadRequestHttpException
     */
    public function getResponseDataWithCheck()
    {
        $this->addHeaders(['Content-Type' => 'application/json']);

        $this->addOptions([
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false, // на платформе самоподписанный сертификат
        ]);

        $response = $this->send();
        $responseData = $response->data;

        if (!$responseData) {
            throw new InvalidCallException($this->_getDebugInfo());
        }

        if (isset($responseData['errors']) && $responseData['errors']) {

            if (isset($responseData['errors']['message'], $responseData['errors']['code'])) {
                $msg = $responseData['errors']['message'];
                $code = $responseData['errors']['code'];
            } else {
                if (isset($responseData['errors'][0], $responseData['errors'][0]['message'])) {
                    $msg = $responseData['errors'][0]['message'];
                    $code = $responseData['errors'][0]['code'];
                } else {
                    $msg = '';
                    $code = 500;
                }
            }

            if (!is_string($msg)) {
                $msg = '';
            }

            throw new InvalidCallException($msg, is_numeric($code) ? $code : -1);
        }

        return $responseData;
    }

    /**
     * @return string
     */
    private function _getDebugInfo()
    {
        $debugInfo = '';
        $debugInfo .= sprintf('url = %s', $this->getUrl()) . PHP_EOL;
        $debugInfo .= sprintf('method = %s', print_r($this->getMethod(), true)) . PHP_EOL;
        $debugInfo .= sprintf('data = %s', print_r($this->getData(), true)) . PHP_EOL;
        $debugInfo .= sprintf('options = %s', print_r($this->getOptions(), true)) . PHP_EOL;
        $debugInfo .= sprintf('headers = %s', print_r($this->getHeaders()->toArray(), true)) . PHP_EOL;
        $debugInfo .= sprintf('requestConfig = %s', print_r($this->client->requestConfig, true)) . PHP_EOL;
        $debugInfo .= sprintf('responseConfig = %s', print_r($this->client->responseConfig, true)) . PHP_EOL;
        return $debugInfo;
    }

    /**
     * Sets the data fields, which composes message content.
     *
     * @param mixed $data content data fields.
     * @return $this self reference.
     */
    public function setData($data)
    {
        $this->setContent(null);
        return parent::setData($data);
    }

    /**
     * @param bool $isCheckOk
     * @return $this
     */
    public function setIsCheckOk($isCheckOk)
    {
        $this->_isCheckOk = $isCheckOk;
        return $this;
    }

}