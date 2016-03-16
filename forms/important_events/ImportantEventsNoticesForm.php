<?php
namespace app\forms\important_events;

use Yii;
use app\classes\validators\ArrayValidator;
use app\classes\Form;
use yii\httpclient\Client;
use yii\httpclient\Response;

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

    public function loadData()
    {
        $client = new Client([
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'format' => Client::FORMAT_URLENCODED
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);

        $auth = [
            'Authorization' => 'Bearer ' . Yii::$app->params['API_SECURE_KEY']
        ];
        if (Yii::$app->params['HTTP_CLIENT_BASIC_AUTH'] === 1) {
            $auth = [
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => Yii::$app->params['HTTP_CLIENT_BASIC_AUTH_USER'] . ':' . Yii::$app->params['HTTP_CLIENT_BASIC_AUTH_PASSWD']
            ];
        }

        /** @var Response $response */
        $response =
            $client
                ->get(
                    Yii::$app->params['TRIGGER_MAILER'] . self::MAILER_METHOD_READ,
                    ['client_account_id' => $this->clientAccountId],
                    $auth
                )
                ->send();

        $this->clientData = [];

        if ($response->isOk) {
            foreach ($response->data as $record) {
                $this->clientData[$record['event_code']] = [
                    'do_email' => $record['do_email'],
                    'do_sms' => $record['do_sms'],
                    'do_lk' => $record['do_lk'],
                ];
            }
        }

        return $this;
    }

    public function saveData()
    {
        $result = [];

        foreach ($this->events as $eventName => $eventData) {
            $row = $eventData;
            $row['event_code'] = $eventName;
            $result[] = $row;
        }

        $client = new Client([
            'requestConfig' => [
                'format' => Client::FORMAT_JSON
            ],
            'responseConfig' => [
                'format' => Client::FORMAT_JSON
            ],
        ]);

        $auth = [
            'Authorization' => 'Bearer ' . Yii::$app->params['API_SECURE_KEY']
        ];
        if (Yii::$app->params['HTTP_CLIENT_BASIC_AUTH'] === 1) {
            $auth = [
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                CURLOPT_USERPWD => Yii::$app->params['HTTP_CLIENT_BASIC_AUTH_USER'] . ':' . Yii::$app->params['HTTP_CLIENT_BASIC_AUTH_PASSWD']
            ];
        }

        /** @var Response $response */
        $response =
            $client
                ->post(
                    Yii::$app->params['TRIGGER_MAILER'] . self::MAILER_METHOD_UPDATE,
                    ['client_account_id' => $this->clientAccountId, $result],
                    $auth
                )
                ->send();

        return $response->isOk;
    }

}