<?php

namespace app\classes;

use Yii;
use yii\base\InvalidCallException;
use yii\httpclient\Response;
use yii\web\BadRequestHttpException;

class HttpRequest extends \yii\httpclient\Request
{
    const ERROR_CODE_OTHER_ACCOUNT = 514; // Did|Vpbx используется на другом клиенте
    const ERROR_CODE_WRONG_DID = 539; // Did|Vpbx не найден
    const ERROR_CODE_DB = 580; // Ошибка сохранения в базу, если возвращается из-за исключения - будет код и сообщение от исключения.
    const ERROR_CODE_ALREADY_PHONE = 581; // Услуга уже подключена данному клиенту (Телефония)
    const ERROR_CODE_ALREADY_VATS = 503; // Услуга уже подключена данному клиенту (ВАТС)
    const ERROR_CODE_WRONG_ACCOUNT = 583; // Клиент (account_id) не найден
    const ERROR_CODE_OTHER_DID = 584; // Did НЕ MCN
    const ERROR_CODE_TRANSFER = 585; // Ошибка переноса услуги
    const ERROR_CODE_WRONG_USAGE = 591; // Did у конкретного клиента не найден

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
            case 'basic':
                $this->addOptions([
                    CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                    CURLOPT_USERPWD =>
                        (isset($config['user']) ? $config['user'] : '') .
                        ':' .
                        (isset($config['passwd']) ? $config['passwd'] : ''),
                ]);
                break;

            case 'bearer':
                $this->addHeaders([
                    'Authorization' => 'Bearer ' . (isset($config['token']) ? $config['token'] : ''),
                ]);
                break;
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
        $handlerLogger = HandlerLogger::me();
        $handlerLogger->add($debugInfoRequest);

        $response = parent::send();

        try {
            $debugInfoResponse = sprintf('Response = %s', print_r($response->getData(), true)) . PHP_EOL;
        } catch (\Exception $e) {
            // сюда попадаем, когда data не может распарситься как JSON. Тогда сохраняем весь response
            $debugInfoResponse = sprintf('Response = %s', $response->getContent()) . PHP_EOL;
        }

        Yii::info($debugInfoRequest . PHP_EOL . PHP_EOL . $debugInfoResponse, $logCategory);
        $handlerLogger->add($debugInfoResponse);

        if ($this->_isCheckOk && !$response->getIsOk()) {
            throw new BadRequestHttpException($debugInfoRequest . PHP_EOL . PHP_EOL . $debugInfoResponse);
        }

        return $response;
    }

    /**
     * Выполнить запрос, проверить ответ и вернуть полученные данные
     *
     * @param null|string $requestCheckType тип проверяемого запроса. Для обработки спецефических исключений
     * @return mixed
     */
    public function getResponseDataWithCheck($requestCheckType = null)
    {
        $this->addHeaders(['Content-Type' => 'application/json']);

        $this->addOptions([
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 300,
            CURLOPT_SSL_VERIFYPEER => false, // на платформе самоподписанный сертификат
        ]);

        $response = $this->send();

        // исключительная обраотка запросов к VPS.
        if ($requestCheckType == 'vps') {

            // Ответ с переводом строки считается как "выполненно"
            if ($response->content == "\n") {
                return ['doc' => 'ok'];
            }
        }

        $responseData = $response->data;

        if (!$responseData) {
            throw new InvalidCallException($this->_getDebugInfo());
        }

        self::recognizeAnError($responseData);


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

    /**
     * Распознавание ошибки в ответе
     *
     * @param mixed $responseData
     * @return array
     */
    public static function recognizeAnError($responseData)
    {
        $code = -1;
        $msg = '';

        // платформа
        if (isset($responseData['errors']) && $responseData['errors']) {

            if (isset($responseData['errors']['message'], $responseData['errors']['code'])) {
                $msg = $responseData['errors']['message'];
                $code = $responseData['errors']['code'];
            } else {
                if (isset($responseData['errors'][0], $responseData['errors'][0]['message'])) {
                    $msg = $responseData['errors'][0]['message'];
                    $code = $responseData['errors'][0]['code'];
                } else {
                    $msg = print_r($responseData, true);
                    $code = 500;
                }
            }

            if (!is_string($msg)) {
                $msg = '';
            }

            if (!is_numeric($code)) {
                $code = -1;
            }

            if (!in_array($code, [self::ERROR_CODE_ALREADY_PHONE, self::ERROR_CODE_ALREADY_VATS])) {
                // если услуга уже создана, но предыдущий запрос закончился таймаутом, то это не ошибка
                // все остальное - ошибка
                throw new InvalidCallException($msg, $code);
            }
        }

        // VPS
        if (isset($responseData['error']) && $responseData['error']) {

            if (isset($responseData['error']['msg'], $responseData['error']['code'])) {
                $msg = $responseData['error']['msg'];
                $code = $responseData['error']['code'];
            } else {
                $msg = print_r($responseData, true);
                $code = 500;
            }

            if (!is_string($msg)) {
                $msg = '';
            }

            if (!is_numeric($code)) {
                $code = -1;
            }

            throw new InvalidCallException($msg, $code);
        }

        return [$code, $msg];
    }

}
