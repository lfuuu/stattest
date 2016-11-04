<?php

namespace app\models\important_events;

use Yii;
use DateTime;
use DateTimeZone;
use ReflectionClass;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use app\exceptions\FormValidationException;
use app\helpers\DateTimeZoneHelper;
use app\classes\IpUtils;
use app\classes\validators\ArrayValidator;
use app\classes\traits\TagsTrait;
use app\models\ClientAccount;
use app\models\TagsResource;
use yii\helpers\ArrayHelper;

/**
 * Class ImportantEvents
 * @property int id
 * @property int date
 * @property int client_id
 * @property string event
 * @property int source_id
 * @property string comment
 * @property ImportantEventsProperties properties
 * @package app\models\important_events
 */
class ImportantEvents extends ActiveRecord
{

    const ROWS_PER_PAGE = 50;

    public
        $propertiesCollection = [],
        $tags_filter = []; // Входящий параметр, aka Database field name

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['event', 'source_id',], 'required', 'on' => 'create'],
            [['event',], 'trim', 'on' => 'create'],
            ['source_id', 'integer', 'on' => 'create'],
            [['event', 'source_id', 'tags_filter'], ArrayValidator::className(), 'on' => 'default'],
            ['client_id', 'integer', 'integerOnly' => true],
            [['comment', 'context',],'string'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            'create' => ['event', 'source', 'client_id', 'extends_data', ],
            'default' => ['event', 'client_id', 'date', 'source_id', 'tags_filter', ],
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
            'comment' => 'Комментарий',
            'tags_filter' => 'Метки',
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
        $event->date = (new DateTime($date,
            new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $event->event = $eventType;
        $event->from_ip = (is_a(Yii::$app, 'yii\web\Application') ? IpUtils::dtr_pton(Yii::$app->request->userIP) : null);

        $source = ImportantEventsSources::findOne(['code' => $eventSource]);
        if (!($source instanceof ImportantEventsSources)) {
            $source = new ImportantEventsSources;
            $source->code = $eventSource;
            $source->save();
        }

        $event->source_id = $source->id;

        foreach ($data as $key => $value) {
            if (array_key_exists($key, $event->attributes)) {
                $event->{$key} = $value;
                unset($data[$key]);
            }
        }

        if ((int)$event->client_id) {
            $data['balance'] = $event->getBalance();
        }

        $event->context = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

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
        if (!empty($this->context) && !$this->propertiesCollection) {
            $this->propertiesCollection = json_decode($this->context);
        }

        return $this->propertiesCollection;
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
     * @return array
     */
    public function getTagList()
    {
        return ArrayHelper::map(
            TagsResource::getTagList((new ReflectionClass(ImportantEventsNames::className()))->getShortName(), 'id'),
            'id',
            'name'
        );
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return (!is_null($this->name) ? $this->name->tags : []);
    }

    /**
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        global $fixclient_data;

        $query =
            self::find()
                ->joinWith('name')
                ->joinWith('clientAccount')
                ->where(['IS NOT', ImportantEventsNames::tableName() . '.id', null])
                ->orderBy([self::tableName() . '.date' => SORT_DESC]);

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

        if ((int)$this->client_id) {
            $query->andFilterWhere(['client_id' => $this->client_id]);
        }
        if (is_array($this->event) && count($this->event)) {
            $query->andFilterWhere(['IN', 'event', (array)$this->event]);
        }
        if (is_array($this->source_id) && count($this->source_id)) {
            $query->andFilterWhere(['IN', 'source_id', (array)$this->source_id]);
        }
        if (is_array($this->tags_filter) && count($this->tags_filter)) {
            $query->innerJoin(
                ['tags' => TagsResource::tableName()],
                '
                    tags.resource = :resource
                    AND tags.resource_id = ' . ImportantEventsNames::tableName() . '.id
                ',
                [
                    'resource' => (new ReflectionClass(ImportantEventsNames::className()))->getShortName(),
                ]
            );
            $query->andWhere(['IN', 'tags.tag_id', $this->tags_filter]);
        }

        list($filter_from, $filter_to) = preg_split('#\s\-\s#', $this->date);

        $filter_from =
            (new DateTime($filter_from, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->setTime(0, 0, 0)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);
        $filter_to =
            (new DateTime($filter_to, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
                ->setTime(23, 59, 59)
                ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $query->andFilterWhere(['BETWEEN', 'date', $filter_from, $filter_to]);

        $query->orderBy(['date' => SORT_DESC]);

        return $dataProvider;
    }

    /**
     * @return float
     */
    private function getBalance()
    {
        $clientAccount = ClientAccount::findOne(['id' => (int)$this->client_id]);
        if (!is_null($clientAccount)) {
            return $clientAccount->billingCounters->realtimeBalance;
        }
        return 0;
    }

}