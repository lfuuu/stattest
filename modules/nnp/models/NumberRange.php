<?php

namespace app\modules\nnp\models;

use app\classes\Connection;
use app\classes\helpers\DependecyHelper;
use app\classes\model\ActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use app\classes\traits\GetUpdateUserTrait;
use app\models\billing\InstanceSettings;
use app\models\Number;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $country_code
 * @property int $ndc
 * @property int $ndc_str
 * @property int $number_from bigint
 * @property int $number_to bigint
 * @property int $full_number_from bigint
 * @property int $full_number_to bigint
 *
 * @property string $operator_source
 * @property int $operator_id
 *
 * @property string $region_source
 * @property int $region_id
 *
 * @property string $city_source
 * @property int $city_id
 *
 * @property string $ndc_type_source
 * @property int $ndc_type_id
 *
 * @property bool $is_active
 * @property string $date_stop date
 * @property string $date_resolution date
 * @property string $detail_resolution
 * @property string $status_number
 * @property string $is_valid
 *
 * @property-read Operator $operator
 * @property-read Region $region
 * @property-read City $city
 * @property-read NumberRangePrefix[] $numberRangePrefixes
 * @property-read NdcType $ndcType
 * @property-read Country $country
 */
class NumberRange extends ActiveRecord
{
    use GetInsertUserTrait;
    use GetUpdateUserTrait;

    const DEFAULT_MOSCOW_NDC = 495;

    private static $_triggerTables = [
        'nnp.country',
        'nnp.destination',
        'nnp.number_range',
        'nnp.number_range_prefix',
        'nnp.operator',
        'nnp.prefix',
        'nnp.prefix_destination',
        'nnp.region',
    ];

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'country_code' => 'Страна',
            'ndc' => 'NDC',
            'ndc_str' => 'NDC',
            'number_from' => 'Номер от',
            'number_to' => 'Номер до',
            'full_number_from' => 'Полный номер от',
            'full_number_to' => 'Полный номер до',

            'operator_source' => 'Исходный оператор',
            'operator_id' => 'Оператор',

            'region_source' => 'Исходный регион',
            'region_id' => 'Регион',

            'city_source' => 'Исходный город',
            'city_id' => 'Город',

            'ndc_type_source' => 'Исходный тип NDC',
            'ndc_type_id' => 'Тип NDC',

            'is_active' => 'Вкл.',
            'date_stop' => 'Дата выключения',
            'date_resolution' => 'Дата выделения диапазона',
            'detail_resolution' => 'Комментарий',
            'status_number' => 'Статус номера',

            'insert_time' => 'Когда создал',
            'insert_user_id' => 'Кто создал',
            'update_time' => 'Когда редактировал',
            'update_user_id' => 'Кто редактировал',

            'is_valid' => 'Подтверждён',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                // Установить "когда создал" и "когда обновил"
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'insert_time',
                'updatedAtAttribute' => 'update_time',
                'value' => new Expression("NOW() AT TIME ZONE 'utc'"), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
            ],
            [
                // Установить "кто создал" и "кто обновил"
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['insert_user_id', 'update_user_id'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'update_user_id',
                ],
                'value' => Yii::$app->user->getId(),
            ],
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.number_range';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_id', 'region_id', 'city_id', 'ndc_type_id'], 'integer'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDbSlave()
    {
        return Yii::$app->dbPgNnpSlave;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->id);
    }

    /**
     * @param int $id
     * @return string
     */
    public static function getUrlById($id)
    {
        return Url::to(['/nnp/number-range/edit', 'id' => $id]);
    }

    /**
     * @return ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(Operator::class, ['id' => 'operator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::class, ['id' => 'region_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::class, ['id' => 'city_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNdcType()
    {
        return $this->hasOne(NdcType::class, ['id' => 'ndc_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumberRangePrefixes()
    {
        return $this->hasMany(NumberRangePrefix::class, ['number_range_id' => 'id']);
    }

    /**
     * Выключить триггеры
     *
     * @throws \yii\db\Exception
     */
    public static function disableTrigger()
    {
        /** @var Connection $db */
        $db = Yii::$app->dbPgNnp;

        foreach (self::$_triggerTables as $triggerTable) {
            $sql = sprintf("SELECT nnp.disable_trigger('%s','notify')", $triggerTable);
            $db->createCommand($sql)->execute();
        }
    }

    /**
     * Включить триггеры и синхронизировать данные по региональным серверам
     *
     * @throws \yii\db\Exception
     */
    public static function enableTrigger()
    {
        /** @var Connection $db */
        $db = Yii::$app->dbPgNnp;

        foreach (self::$_triggerTables as $triggerTable) {
            $sql = sprintf("SELECT nnp.enable_trigger('%s','notify')", $triggerTable);
            $db->createCommand($sql)->execute();
        }

        // синхронизировать данные по региональным серверам
        $sql = 'SELECT FROM event.notify_nnp_all(:p_server_id)';
        $activeQuery = InstanceSettings::find()
            ->where(['active' => true]);
        foreach ($activeQuery->each() as $instanceSettings) {
            $db->createCommand($sql, [':p_server_id' => $instanceSettings->id])->execute();
        }
    }

    /**
     * Запуск синхронизации ННП
     *
     * * @throws \yii\db\Exception
     */
    public static function syncNnpAll()
    {
        /** @var Connection $db */
        $db = Yii::$app->dbPgNnp;

        $db->createCommand('SELECT event.notify_nnp_all()')->execute();
    }

    /**
     * Включен триггер?
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function isTriggerEnabled()
    {
        /** @var Connection $db */
        $db = Yii::$app->dbPgNnp;

        $triggerTable = reset(self::$_triggerTables);
        $sql = sprintf("SELECT nnp.is_trigger_enabled('%s','notify')", $triggerTable);
        return $db->createCommand($sql)->queryScalar();
    }

    /**
     * Список NDC
     *
     * @param int $countryCode
     * @param int $cityId
     * @param int $ndcTypeId
     * @return \integer[]
     */
    public static function getNdcList($countryCode, $cityId, $ndcTypeId)
    {
        static $_cache = [];

        if (!isset($_cache[$countryCode][$cityId])) {

            $cache = \Yii::$app->cache;

//            $data = $cache->delete('ndcdata');
            $data = $cache->get('ndcdata');

            if (!$data) {
                self::_fillNdcData();
                $data = $cache->get('ndcdata');
            }

            if ($data) {
                if ($cityId) {
                    $dd = $data['with_city_id'][$countryCode][$ndcTypeId][$cityId];
                } else {
//                    $dd = array_unique($data['without_city_id'][$countryCode][$ndcTypeId]);
                    $dd = $data['without_city_id'][$countryCode][$ndcTypeId];
                }

                if (!$dd) {
                    $dd = [];
                }
                return array_combine($dd, $dd);
            }

            $where = [
                'country_code' => $countryCode,
                'ndc_type_id' => $ndcTypeId,
                'is_active' => true
            ];

            $cityId && $where['city_id'] = $cityId;

            $_cache[$countryCode][$cityId] = NumberRange::find()
                ->select('ndc_str')
                ->distinct()
                ->where($where)
                ->indexBy('ndc_str')
                ->orderBy(['ndc_str' => SORT_ASC])
                ->column(NumberRange::getDbSlave());
        }

        return $_cache[$countryCode][$cityId];
    }

    private static function _fillNdcData()
    {
        $dataAll = [];
        $dataWithCity = [];

        $rows = NumberRange::find()->alias('nr')
            ->select(['nr.country_code', 'city_id', 'ndc_type_id', 'ndc_str', 'r.name'])
            ->joinWith('region r')
            ->where(['is_active' => true/*, 'country_code' => Country::RUSSIA*/])
            ->groupBy(['nr.country_code', 'city_id', 'ndc_type_id', 'ndc_str', 'r.name'])
            ->orderBy([
                'country_code' => SORT_ASC,
                'ndc_type_id' => SORT_ASC,
                'ndc_str' => SORT_ASC,
            ])->asArray()->all(NumberRange::getDbSlave());


        foreach ($rows as $row) {
            if (!$row['ndc_str']) {
                continue;
            }

            if (!isset($dataAll[$row['country_code']][$row['ndc_type_id']])) {
                $dataAll[$row['country_code']][$row['ndc_type_id']] = [];
            }

            $cityName = $row['name'] ? ' (' . $row['name'] . ')' : null;
            $dataAll[$row['country_code']][$row['ndc_type_id']][$row['ndc_str']] = $row['ndc_str'] . ($cityName ?? '');

            if ($row['city_id']) {
                $dataWithCity[$row['country_code']][$row['ndc_type_id']][$row['city_id']][$row['ndc_str']] = $row['ndc_str'] . ($cityName ?? '');
            }
        }

        $cache = \Yii::$app->cache;
        $cache->set('ndcdata', ['with_city_id' => $dataWithCity, 'without_city_id' => $dataAll], DependecyHelper::TIMELIFE_DAY);
    }

    /**
     * @param string $number
     * @return NumberRange
     */
    public static function getByNumber($number)
    {
        NumberRange::setPgTimeout(NumberRange::PG_CALCULATE_RESOURCE_TIMEOUT, NumberRange::getDbSlave());

        return NumberRange::find()
            ->andWhere(['is_active' => true])
            ->andWhere(['<=', 'full_number_from', $number])
            ->andWhere(['>=', 'full_number_to', $number])
            ->orderBy(new Expression('ndc IS NOT NULL DESC')) // чтобы большой диапазон по всей стране типа 0000-9999 был в конце
            ->one(NumberRange::getDbSlave());
    }

    /**
     * Получение NNP информации по номеру.
     *
     * @param string $number
     * @return array
     */
    public static function getNumberInfo($number)
    {
        $numberModel = $number;
        if (!($number instanceof \app\models\Number)) {
            $numberModel = \app\models\Number::findOne(['number' => $number]);
        }
        if (!$numberModel) {
            return [];
        }

        $toReturn = [];
        $billerInfo = Number::getNnpInfo($numberModel->number);
        if (!isset($billerInfo)) {
            $nnpNumberRange = self::getByNumber($numberModel->number);
        } else {
            $nnpNumberRange = new NumberRange();
            $nnpNumberRange->setAttributes([
                'ndc' => $billerInfo['ndc'],
                'ndc_type_id' => $billerInfo['ndc_type_id'],
                'country_prefix' => $billerInfo['country_prefix'],
                'operator_id' => $billerInfo['nnp_operator_id'],
                'region_id' => (string)$billerInfo['nnp_region_id'],
                'city_id' => $billerInfo['nnp_city_id'],
                'country_code' => $billerInfo['country_code'],
            ], false);
        }

        if (!isset($nnpNumberRange->ndc)) {
            $toReturn['ndc'] = (string)$numberModel->ndc;
            $toReturn['ndc_type'] = (int)$numberModel->ndc_type_id;
        } else {
            $toReturn['ndc'] = (string)$nnpNumberRange->ndc;
            $toReturn['ndc_type'] = (int)$nnpNumberRange->ndc_type_id;
        }

        $toReturn['country_name'] = $nnpNumberRange->country->name_eng;
        $toReturn['country_prefix'] = $nnpNumberRange->country->prefix;
        $toReturn['region_name'] = $nnpNumberRange->region->name;
        $toReturn['operator'] = $nnpNumberRange->operator->name;
        $toReturn['city_name'] = $nnpNumberRange->city->name;
        $toReturn['number_length'] = $numberModel->city->postfix_length;
        $toReturn['city_id'] = $billerInfo['nnp_city_id'];
        $toReturn['country_code'] = $billerInfo['country_code'];

        return $toReturn;
    }

    public static function formatNumber(NumberRange $row, $isFrom = true)
    {
        if ($isFrom) {
            $number = (string)$row->number_from;
            $fullNumber = (string)$row->full_number_from;
        } else {
            $number = (string)$row->number_to;
            $fullNumber = (string)$row->full_number_to;
        }

        $vv = strpos($fullNumber, $row->ndc_str);

        if ($vv === false) {
            return $fullNumber;
        }

        $value = substr($fullNumber, 0, $vv) . ' (' . $row->ndc_str . ') ';

        $s = substr($fullNumber, strlen($row->ndc_str) + $vv);
        $v = '';

        $count = 0;
        while (strlen($s) > 4) {
            $v = substr($s, -2) . ($v ? '-' . $v : '');
            $s = substr($s, 0, strlen($s) - 2);
            if ($count++ > 10) {
                break;
            }
        }
        $value .= $s . '-' . $v;

        return $value;
    }
}
