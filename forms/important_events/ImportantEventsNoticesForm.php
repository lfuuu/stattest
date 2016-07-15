<?php
namespace app\forms\important_events;

use Yii;
use app\classes\validators\ArrayValidator;
use app\classes\Form;
use app\classes\HttpClient;
use app\models\Language;
use app\models\ClientAccountOptions;
use app\forms\client\ClientAccountOptionsForm;
use yii\base\InvalidConfigException;

class ImportantEventsNoticesForm extends Form
{

    const MAILER_METHOD_READ = '/site/events';
    const MAILER_METHOD_UPDATE = '/site/events-set';

    public
        $clientAccountId,
        $clientData,
        $events,
        $language;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            ['clientAccountId', 'integer'],
            ['clientAccountId', 'required'],
            ['events', ArrayValidator::className()],
            ['language', 'in', 'range' => array_keys(Language::getList())],
            ['language', 'default', 'value' => Language::LANGUAGE_DEFAULT],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [];
    }

    /**
     * Загрузка данных для формы
     *
     * @return array|bool
     */
    public function loadData()
    {
        if (!$this->validateConfig()) {
            return false;
        }

        $config = Yii::$app->params['MAILER'];

        if (!isset($config, $config['url'])) {
            throw new InvalidConfigException('Mailer was not configured');
        }

        $client = new HttpClient;
        $client->setTransport(\yii\httpclient\CurlTransport::class);
        $client->requestConfig = [
            'format' => HttpClient::FORMAT_RAW_URLENCODED,
        ];
        $client->responseConfig = [
            'format' => HttpClient::FORMAT_JSON,
        ];
        $request = $client
            ->createRequest()
            ->setMethod('get')
            ->setData(['clientAccountId' => $this->clientAccountId])
            ->setUrl($config['url'] . self::MAILER_METHOD_READ);

        if (isset($config['auth'])) {
            $client->auth($request, $config['auth']);
        }

        /** @var \yii\httpclient\Response $response */
        try {
            $response = $client->send($request);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error',
                'Отсутствует соединение с MAILER' . PHP_EOL . '<br />Ошибка: ' . $e->getMessage());
            return false;
        }

        if (!$response->isOk) {
            Yii::$app->session->addFlash('error', 'Ошибка работы с MAILER. Код ошибки:' . $response->getStatusCode());
            return false;
        }

        if (!is_array($response->data)) {
            Yii::$app->session->addFlash('error', 'Ошибка формата данных MAILER');
            return false;
        }

        $result = [];

        foreach ($response->data as $record) {
            $result[] = [
                'event' => $record['event_code'],
                'group_id' => $record['group_id'],
                'do_email_monitoring' => $record['do_email_monitoring'],
                'do_email_operator' => $record['do_email_operator'],
                'do_email' => $record['do_email'],
                'do_email_personal' => $record['do_email_personal'],
                'do_sms' => $record['do_sms'],
                'do_sms_personal' => $record['do_sms_personal'],
                'do_lk' => $record['do_lk'],
            ];
        }

        return $result;
    }

    /**
     * Сохранение формы
     *
     * @return bool
     */
    public function saveData()
    {
        if (!$this->validateConfig()) {
            return false;
        }

        $config = Yii::$app->params['MAILER'];

        if (!isset($config, $config['url'])) {
            throw new InvalidConfigException('Mailer was not configured');
        }

        (new ClientAccountOptionsForm)
            ->setClientAccountId($this->clientAccountId)
            ->setOption(ClientAccountOptions::OPTION_MAIL_DELIVERY_LANGUAGE)
            ->setValue($this->language)
            ->save($deleteExisting = true);

        $result = [];

        foreach ($this->events as $eventName => $eventData) {
            $row = $eventData;
            $row['event_code'] = $eventName;
            $result[] = $row;
        }

        $client = new HttpClient;
        $client->setTransport(\yii\httpclient\CurlTransport::class);
        $client->requestConfig = [
            'format' => HttpClient::FORMAT_JSON,
        ];
        $client->responseConfig = [
            'format' => HttpClient::FORMAT_JSON,
        ];
        $request = $client
            ->createRequest()
            ->setMethod('post')
            ->setData($result)
            ->setUrl($config['url'] . self::MAILER_METHOD_UPDATE . '?clientAccountId=' . $this->clientAccountId);

        if (isset($config['auth'])) {
            $client->auth($request, $config['auth']);
        }

        /** @var \yii\httpclient\Response $response */
        try {
            $response = $client->send($request);
        } catch (\Exception $e) {
            Yii::$app->session->addFlash('error',
                'Отсутствует соединение с MAILER' . PHP_EOL . '<br />Ошибка: ' . $e->getMessage());
            return false;
        }

        if (!$response->isOk) {
            Yii::$app->session->addFlash('error', 'Ошибка работы с MAILER. Код ошибки:' . $response->getStatusCode());
            return false;
        }

        if (!is_array($response->data) || empty($response->data['count'])) {
            Yii::$app->session->addFlash('error', 'Ошибка формата данных MAILER');
            return false;
        }

        Yii::$app->session->addFlash('success', 'Данные успешно обновлены ( ' . $response->data['count'] . ' позиций)');
        return true;
    }

    /**
     * Проверка наличия конфигурации
     *
     * @return bool
     */
    private function validateConfig()
    {
        if (!isset(Yii::$app->params['MAILER'], Yii::$app->params['MAILER']['url'])) {
            Yii::$app->session->addFlash('error', 'Отсутствует конфигурация для MAILER');
            return false;
        }
        return true;
    }

}