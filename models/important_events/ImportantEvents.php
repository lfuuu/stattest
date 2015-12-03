<?php

namespace app\models\important_events;

use Yii;
use DateTime;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\exceptions\FormValidationException;
use yii\helpers\ArrayHelper;
use app\models\ClientAccount;
use app\models\ClientCounter;

class ImportantEvents extends ActiveRecord
{

    public $propertiesCollection = [];

    public function rules()
    {
        return [
            [['event', 'source', ], 'required', 'on' => 'create'],
            [['event', 'source', ], 'string'],
            [['event', 'source', ], 'trim'],
            ['client_id', 'required', 'on' => 'create', 'when' => function($model) {
                return in_array($model->event, ArrayHelper::getColumn(ImportantEventsNames::find()->all(), 'code'), true);
            }],
            ['client_id', 'integer', 'integerOnly' => true],
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

    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\ImportantEvents::className(),
        ];
    }

    public static function tableName()
    {
        return 'important_events';
    }

    /**
     * @param $eventType
     * @param array $data
     * @param string $date
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public static function create($eventType, $data = [], $date = 'now')
    {
        $event = new self;

        $event->scenario = 'create';
        $event->date = (new DateTime($date))->format('Y-m-d H:i:s');
        $event->event = $eventType;

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $event->attributes)) {
                $event->propertiesCollection[] = [0, $key, $value];
            }
            else {
                $event->{$key} = $value;
            }
        }

        if ((int) $event->client_id) {
            $event->propertiesCollection[] = [0, 'balance', $event->getBalance()];
        }

        if (!($event->validate() && $event->save())) {
            throw new FormValidationException($event);
        }

        return false;
    }

    /**
     * @return ImportantEventsNames
     */
    public function getName()
    {
        return $this->hasOne(ImportantEventsNames::className(), ['code' => 'event']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(ImportantEventsProperties::className(), ['event_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRules()
    {
        return $this->hasMany(ImportantEventsRules::className(), ['event' => 'event']);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $name = ImportantEventsNames::findOne(['code' => $this->event]);
        return ($name instanceof ImportantEventsNames ? $name->value : $this->event);
    }

    /**
     * @param $clientId
     * @return float
     */
    private function getBalance()
    {
        $client = ClientAccount::findOne($this->client_id);
        $balance = $client->balance;
        if ($client->credit > -1) {
            $clientAmountSum = ClientCounter::dao()->getAmountSumByAccountId($client->id);
            $balance += $clientAmountSum['amount_sum'];
        }
        return $balance;
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

}