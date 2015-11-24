<?php

namespace app\models;

use Yii;
use DateTime;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\exceptions\FormValidationException;

class ImportantEvents extends ActiveRecord
{

    const EVENT_ZERO_BALANCE = 'zero_balance';
    const EVENT_UNSET_ZERO_BALANCE = 'unset_zero_balance';
    const EVENT_ADD_PAYMENT = 'add_pay_notif';
    const EVENT_MIN_BALANCE = 'min_balance';
    const EVENT_UNSET_MIN_BALANCE = 'unset_min_balance';
    const EVENT_DAY_LIMIT = 'day_limit';

    public static $eventsList = [
        self::EVENT_ZERO_BALANCE => 'Финансовая блокировка',
        self::EVENT_UNSET_ZERO_BALANCE => 'Снятие: Финансовая блокировка',
        self::EVENT_ADD_PAYMENT => 'Зачисление средств',
        self::EVENT_MIN_BALANCE => 'Критический остаток',
        self::EVENT_UNSET_MIN_BALANCE => 'Снятие: Критический остаток',
        self::EVENT_DAY_LIMIT => 'Суточный лимит',
    ];

    public function rules()
    {
        return [
            [['event', 'source', ], 'required', 'on' => 'create'],
            [['event', 'source'], 'string'],
            ['client_id', 'required', 'on' => 'create', 'when' => function($model) {
                return in_array($model->event, [
                    self::EVENT_ZERO_BALANCE,
                    self::EVENT_UNSET_ZERO_BALANCE,
                    self::EVENT_ADD_PAYMENT,
                    self::EVENT_MIN_BALANCE,
                    self::EVENT_UNSET_MIN_BALANCE,
                    self::EVENT_DAY_LIMIT,
                ]);
            }],
            ['client_id', 'integer', 'integerOnly' => true],
            ['extends_data', 'exist', 'allowArray' => true, 'when' => function($model, $attribute) {
                return is_array($model->$attribute);
            }],
        ];
    }

    public function scenarios()
    {
        return [
            'create' => ['event', 'source', 'client_id', 'extends_data'],
            'default' => ['event', 'client_id', 'date'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'client_id' => 'Клиент',
            'date' => 'Когда произошло',
            'event' => 'Событие',
            'balance' => 'Баланс',
            'limit' => 'Лимит',
            'value' => 'Значение',
        ];
    }

    public static function tableName()
    {
        return 'important_events';
    }

    public static function create($eventType, $data = [], $date = 'now')
    {
        $event = new self;

        $event->scenario = 'create';
        $event->date = (new DateTime($date))->format('Y-m-d H:i:s');
        $event->event = $eventType;

        $extendsData = [];
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $event->attributes)) {
                $extendsData[$key] = $value;
            }
            else {
                $event->{$key} = $value;
            }
        }

        if ((int) $event->client_id) {
            $extendsData['balance'] = self::getBalance($event->client_id);
        }

        $event->extends_data = json_encode($extendsData);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($event->validate() && $event->save()) {
                $transaction->commit();
                return true;
            }
            else {
                throw new FormValidationException($event);
            }
        }
        catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return false;
    }

    /**
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        global $fixclient_data;

        $query = self::find()->orderBy('date DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->sort = false;

        if (!($this->load($params) && $this->validate())) {
            if ($fixclient_data) {
                $this->client_id = $fixclient_data['id'];
                $query->andFilterWhere([
                    'client_id' => $this->client_id,
                ]);
            }

            return $dataProvider;
        }

        $query->andFilterWhere([
            'client_id' => $this->client_id,
            'event' => $this->event,
        ]);

        $query->andFilterWhere(array_merge(['between', 'date'], preg_split('#\s\-\s#', $this->date)));

        return $dataProvider;
    }

    /**
     * @param $clientId
     * @return float
     */
    private static function getBalance($clientId)
    {
        $client = ClientAccount::findOne($clientId);
        $balance = $client->balance;
        if ($client->credit > -1) {
            $clientAmountSum = ClientCounter::dao()->getAmountSumByAccountId($client->id);
            $balance += $clientAmountSum['amount_sum'];
        }
        return $balance;
    }

}