<?php

namespace app\models;

use Yii;
use DateTime;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\exceptions\FormValidationException;
use yii\helpers\ArrayHelper;

class ImportantEvents extends ActiveRecord
{

    public function rules()
    {
        return [
            [['event', 'source', ], 'required', 'on' => 'create'],
            [['event', 'source'], 'string'],
            ['client_id', 'required', 'on' => 'create', 'when' => function($model) {
                return in_array($model->event, ArrayHelper::getColumn(ImportantEventsNames::find()->all(), 'code'));
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

    public static function tableName()
    {
        return 'important_events';
    }

    public function getName()
    {
        return $this->hasOne(ImportantEventsNames::className(), ['code' => 'event']);
    }

    public function getProperties()
    {
        return $this->hasMany(ImportantEventsProperties::className(), ['event_id' => 'id']);
    }

    public static function create($eventType, $data = [], $date = 'now')
    {
        $event = new self;

        $event->scenario = 'create';
        $event->date = (new DateTime($date))->format('Y-m-d H:i:s');
        $event->event = $eventType;

        $properties = [];
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $event->attributes)) {
                $properties[] = [0, $key, $value];
            }
            else {
                $event->{$key} = $value;
            }
        }

        if ((int) $event->client_id) {
            $properties[] = [0, 'balance', self::getBalance($event->client_id)];
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($event->validate() && $event->save()) {
                if (count($properties)) {
                    Yii::$app->db->createCommand()->batchInsert(
                        ImportantEventsProperties::tableName(),
                        ['event_id', 'property', 'value'],
                        array_map(function($row) use ($event) { $row[0] = $event->id; return $row; }, $properties)
                    )->execute();
                }

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