<?php
namespace app\forms\important_events;

use Yii;
use app\classes\validators\ArrayValidator;
use app\classes\Form;
use app\classes\HttpClient;
use yii\helpers\ArrayHelper;
use app\models\important_events\ImportantEventsNames;

class ImportantEventsNoticesForm extends Form
{

    const MAILER_METHOD_READ = '/site/accesslistjson';
    const MAILER_METHOD_UPDATE = '/site/accesssetjson?';

    public
        $clientAccountId,
        $clientData,
        $events;

    public function rules()
    {
        return [
            ['clientAccountId', 'integer'],
            ['clientAccountId', 'required'],
            ['events', ArrayValidator::className()],
        ];
    }

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

        $client = new HttpClient([
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'format' => HttpClient::FORMAT_URLENCODED
            ],
            'responseConfig' => [
                'format' => HttpClient::FORMAT_JSON
            ],
        ]);

        /** @var \yii\httpclient\Response $response */
        try {
            $response =
                $client
                    ->get(
                        Yii::$app->params['MAILER']['url'] . self::MAILER_METHOD_READ,
                        ['client_account_id' => $this->clientAccountId],
                        [], // Headers array
                        (
                            isset(Yii::$app->params['MAILER'], Yii::$app->params['MAILER']['auth'])
                            ? $client->auth(Yii::$app->params['MAILER']['auth'])
                            : []
                        ) // Options array
                    )
                    ->send();
        }
        catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Отсутствует соединение с MAILER' . PHP_EOL . '<br />Ошибка: ' . $e->getMessage());
            return false;
        }

        $result = [];

        $eventNames = ArrayHelper::map(ImportantEventsNames::find()->select(['code', 'value'])->all(), 'code', 'value');

        if (!$response->isOk) {
            Yii::$app->session->addFlash('error', 'Ошибка работы с MAILER. Ошибка:' . $response->statusCode);
            return false;
        }

        if (!count($response->data)) {
            Yii::$app->session->addFlash('error', 'Ошибка формата данных MAILER');
            return false;
        }

        foreach ($response->data as $record) {
            $result[] = [
                'event_code' => $record['event_code'],
                'event_name' => isset($eventNames[$record['event_code']]) ? $eventNames[$record['event_code']] : $record['event_code'],
                'do_email' => $record['do_email'],
                'do_sms' => $record['do_sms'],
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

        $result = [];

        foreach ($this->events as $eventName => $eventData) {
            $row = $eventData;
            $row['event_code'] = $eventName;
            $result[] = $row;
        }

        $client = new HttpClient([
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'format' => HttpClient::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => HttpClient::FORMAT_JSON
            ],
        ]);

        /** @var \yii\httpclient\Response $response */
        try {
            $response =
                $client
                    ->post(
                        Yii::$app->params['MAILER']['url'] . self::MAILER_METHOD_UPDATE . 'client_account_id=' . $this->clientAccountId,
                        $result,
                        [], // Headers array
                        (
                            isset(Yii::$app->params['MAILER'], Yii::$app->params['MAILER']['auth'])
                            ? $client->auth(Yii::$app->params['MAILER']['auth'])
                            : []
                        ) // Options array
                    )->send();
        }
        catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Отсутствует соединение с MAILER' . PHP_EOL . '<br />Ошибка: ' . $e->getMessage());
            return false;
        }

        if (!$response->isOk) {
            Yii::$app->session->addFlash('error', 'Ошибка работы с MAILER. Ошибка:' . $response->statusCode);
            return false;

        }

        if (!count($response->data) || !$response->data['count']) {
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