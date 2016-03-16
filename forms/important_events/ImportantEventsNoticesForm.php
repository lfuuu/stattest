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
        return [
            'client' => 'Клиент',
            'actual_from' => 'Активна с',
            'actual_to' => 'Активна до',
            'ip' => 'IP',
            'amount' => 'Количество',
            'status' => 'Состояние',
            'comment' => 'Комментарий',
            'tarif_id' => 'Услуга',
            'router' => 'Роутер',
        ];
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
                        (
                        isset(Yii::$app->params['MAILER'], Yii::$app->params['MAILER']['auth'])
                            ? $client->auth(Yii::$app->params['MAILER']['auth'])
                            : []
                        )
                    )
                    ->send();
        }
        catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Отсутствует соединение с MAILER' . PHP_EOL . '<br />Ошибка: ' . $e->getMessage());
            return false;
        }

        $result = [];

        $eventNames = ArrayHelper::map(ImportantEventsNames::find()->select(['code', 'value'])->all(), 'code', 'value');

        if ($response->isOk) {
            foreach ($response->data as $record) {
                $result[] = [
                    'event_code' => $record['event_code'],
                    'event_name' => isset($eventNames[$record['event_code']]) ? $eventNames[$record['event_code']] : $record['event_code'],
                    'do_email' => $record['do_email'],
                    'do_sms' => $record['do_sms'],
                    'do_lk' => $record['do_lk'],
                ];
            }
        }
        else {
            Yii::$app->session->addFlash('error', 'Ошибка работы с MAILER');
            return false;
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
                        (
                        isset(Yii::$app->params['MAILER'], Yii::$app->params['MAILER']['auth'])
                            ? $client->auth(Yii::$app->params['MAILER']['auth'])
                            : []
                        )
                    )->send();
        }
        catch (\Exception $e) {
            Yii::$app->session->addFlash('error', 'Отсутствует соединение с MAILER' . PHP_EOL . '<br />Ошибка: ' . $e->getMessage());
            return false;
        }

        if ($response->isOk) {
            Yii::$app->session->addFlash('success', 'Данные успешно обновлены');
            return true;
        }
        else {
            Yii::$app->session->addFlash('error', 'Ошибка работы с MAILER');
            return false;
        }
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