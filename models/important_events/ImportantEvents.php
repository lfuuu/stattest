<?php

namespace app\models\important_events;

use DateTime;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\dao\important_events\ImportantEventsDao;

class ImportantEvents extends ActiveRecord
{

    public function rules()
    {
        return [
            [['client_id'], 'integer', 'integerOnly' => true],
            ['date', 'date', 'format' => 'yyyy-MM-dd - yyyy-MM-dd'],
            ['event', 'string'],
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

    /**
     * @param int $clientId
     * @param string $eventType
     * @param float $balance
     * @param float $limit
     * @param float $currentValue
     * @param string $date
     * @return ImportantEvents
     * @throws \Exception
     */
    public static function create($clientId, $eventType, $balance, $limit = 0, $currentValue = 0, $date = 'now')
    {
        $event = new self;

        $event->client_id = $clientId;
        $event->date = (new DateTime($date))->format('Y-m-d H:i:s');
        $event->event = $eventType;
        $event->balance = $balance;
        $event->limit = $limit;
        $event->value = $currentValue;

        try {
            return $event->save();
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return ImportantEventsDao
     */
    public static function dao()
    {
        return ImportantEventsDao::me();
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