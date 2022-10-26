<?php

namespace app\models\important_events;

use app\classes\behaviors\ClientBlockedCommentBehavior;
use app\classes\IpUtils;
use app\classes\model\ActiveRecord;
use app\classes\traits\AddClientAccountFilterTraits;
use app\classes\traits\TagsTrait;
use app\classes\validators\ArrayValidator;
use app\exceptions\ModelValidationException;
use app\helpers\DateTimeZoneHelper;
use app\models\ClientAccount;
use app\models\ClientBlockedComment;
use app\models\TagsResource;
use DateTime;
use DateTimeZone;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property int $date
 * @property int $client_id
 * @property string $event
 * @property int $source_id
 * @property int $from_ip
 * @property string $remote_ip
 * @property string $login
 * @property string $comment
 * @property string $context - JSON formatted
 *
 * @property string $title
 * @property-read ImportantEventsNames $name
 * @property-read ClientAccount $clientAccount
 * @property-read ImportantEventsSources $source
 * @property array $tags
 * @property array $tagList
 * @property float $balance
 * @property array $properties
 */
class ImportantEvents extends ActiveRecord
{

    use TagsTrait;
    use AddClientAccountFilterTraits;

    const ROWS_PER_PAGE = 50;

    public $propertiesCollection = [];

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => ClientBlockedCommentBehavior::class
            ]
        ];
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['event', 'source_id',], 'required', 'on' => 'create'],
            [['event',], 'trim', 'on' => 'create'],
            ['source_id', 'integer', 'on' => 'create'],
            [['event', 'source_id', 'tags_filter'], ArrayValidator::class, 'on' => 'default'],
            ['client_id', 'integer', 'integerOnly' => true],
            [['comment', 'context',], 'string'],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return [
            'create' => ['event', 'source', 'client_id', 'extends_data',],
            'default' => ['event', 'client_id', 'date', 'source_id', 'tags_filter',],
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
            'remote_ip' => 'IP клиента',
            'x_fwd_ip' => 'X FWD IP',
            'ip' => 'IP источника',
            'login' => 'Login входа в ЛК'
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
     * @param string $eventType
     * @param string $eventSource
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

        if (isset($data['REMOTE_ADDR']) && $data['REMOTE_ADDR']) {
            $event->remote_ip = $data['REMOTE_ADDR'];
        }

        if ($eventType == ImportantEventsNames::CLIENT_LOGGED_IN && isset($data['is_support']) && !$data['is_support']) {
            if (isset($data['login']) && $data['login']) {
                $event->login = $data['login'];
            }
        }

        $event->context = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if (!($event->validate() && $event->save())) {
            throw new ModelValidationException($event);
        }

        return true;
    }

    /**
     * @return ImportantEventsNames
     */
    public function getName()
    {
        return $this->hasOne(ImportantEventsNames::class, ['code' => 'event']);
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        if (!empty($this->context) && !$this->propertiesCollection) {
            $this->propertiesCollection = json_decode($this->context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return $this->propertiesCollection;
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
        return $this->hasOne(ImportantEventsSources::class, ['id' => 'source_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientAccount()
    {
        return $this->hasOne(ClientAccount::class, ['id' => 'client_id']);
    }

    /**
     * Method overriding
     *
     * @return array
     */
    public function getTagList()
    {
        return ArrayHelper::map(
            TagsResource::getTagList((new ImportantEventsNames)->formName(), 'id'),
            'id',
            'name'
        );
    }

    /**
     * Method overriding
     *
     * @return array
     */
    public function getTags()
    {
        return (!is_null($this->name) ? $this->name->tags : []);
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()
            ->joinWith('name')
            ->joinWith('clientAccount')
            ->where(['IS NOT', ImportantEventsNames::tableName() . '.id', null])
            ->orderBy([self::tableName() . '.date' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pageSize' => self::ROWS_PER_PAGE,
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {

            $this->client_id = $this->_getCurrentClientAccountId();
            if ($this->client_id) {
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

        $this->setTagsFilter($query, ImportantEventsNames::class);

        list($filter_from, $filter_to) = preg_split('#\s\-\s#', $this->date);

        $filter_from = (new DateTime($filter_from, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
            ->setTime(0, 0, 0)
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $filter_to = (new DateTime($filter_to, new DateTimeZone(DateTimeZoneHelper::TIMEZONE_DEFAULT)))
            ->setTime(23, 59, 59)
            ->format(DateTimeZoneHelper::DATETIME_FORMAT);

        $query->andFilterWhere(['BETWEEN', 'date', $filter_from, $filter_to]);

        $query->orderBy(['date' => SORT_DESC]);

        return $dataProvider;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        $clientAccount = ClientAccount::findOne(['id' => (int)$this->client_id]);
        if (!is_null($clientAccount)) {
            return round($clientAccount->billingCounters->realtimeBalance, 2);
        }

        return 0;
    }

}