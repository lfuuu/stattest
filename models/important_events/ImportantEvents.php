<?php

namespace app\models\important_events;

use Yii;
use DateTime;
use DateTimeZone;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\exceptions\FormValidationException;
use app\helpers\DateTimeZoneHelper;
use app\classes\validators\ArrayValidator;
use app\models\ClientAccount;
use app\models\ClientCounter;

/**
 * Class ImportantEvents
 * @property int id
 * @property int date
 * @property int client_id
 * @property string event
 * @property int source_id
 * @property ImportantEventsProperties properties
 * @package app\models\important_events
 */
class ImportantEvents extends ActiveRecord
{

    const ROWS_PER_PAGE = 50;

    public $propertiesCollection = [];

    /*
     * @return array
     */
    public function rules()
    {
        return [
            [['event', 'source_id', ], 'required', 'on' => 'create'],
            [['event', ], 'trim', 'on' => 'create'],
            ['source_id', 'integer', 'on' => 'create'],
            [['event', 'source_id'], ArrayValidator::className(), 'on' => 'default'],
            ['client_id', 'integer', 'integerOnly' => true],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            'create' => ['event', 'source', 'client_id', 'extends_data'],
            'default' => ['event', 'client_id', 'date', 'source_id'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'client_id' => 'Клиент',
            'date' => 'Когда произошло',
            'event' => 'Событие',
            'source_id' => 'Источник',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'ImportantEvents' => \app\classes\behaviors\important_events\ImportantEventsBehavior::className(),
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'important_events';
    }

    /**
     * @param $eventType
     * @param $eventSource
     * @param array $data
     * @param string $date
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public static function create($eventType, $eventSource, $data = [], $date = 'now')
    {
        $event = new self;

        $event->scenario = 'create';
        $event->date = (new DateTime($date, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))->format(DateTime::ATOM);
        $event->event = $eventType;

        $source = ImportantEventsSources::findOne(['code' => $eventSource]);
        if (!($source instanceof ImportantEventsSources)) {
            $source = new ImportantEventsSources;
            $source->code = $eventSource;
            $source->save();
        }

        $event->source_id = $source->id;

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

        return true;
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
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(ImportantEventsSources::className(), ['id' => 'source_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::className(), ['id' => 'client_id']);
    }

    /**
     * @param $clientId
     * @return float
     */
    private function getBalance()
    {
        $clientAccount = ClientAccount::findOne($this->client_id);
        return $clientAccount->billingCounters->realtimeBalance;
    }

    /**
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        global $fixclient_data;

        $query =
            self::find()
                ->joinWith('clientAccount')
                ->orderBy(['date' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => self::ROWS_PER_PAGE,
            ],
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

        if ((int) $this->client_id) {
            $query->andFilterWhere(['client_id' => $this->client_id]);
        }
        if (is_array($this->event) && count($this->event)) {
            $query->andFilterWhere(['in', 'event', (array) $this->event]);
        }
        if (is_array($this->source_id) && count($this->source_id)) {
            $query->andFilterWhere(['in', 'source_id', (array) $this->source_id]);
        }

        list($filter_from, $filter_to) = preg_split('#\s\-\s#', $this->date);

        $filter_from =
            (new DateTime($filter_from, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->setTime(0, 0, 0)
                ->format(DateTime::ATOM);
        $filter_to =
            (new DateTime($filter_to, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->setTime(23, 59, 59)
                ->format(DateTime::ATOM);

        $query->andFilterWhere(['between', 'date', $filter_from, $filter_to]);

        $query->orderBy('date DESC');

        return $dataProvider;
    }

}