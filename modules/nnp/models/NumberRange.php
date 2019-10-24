<?php

namespace app\modules\nnp\models;

use app\classes\Connection;
use app\classes\model\ActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use app\classes\traits\GetUpdateUserTrait;
use app\models\billing\InstanceSettings;
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
                ->column();
        }

        return $_cache[$countryCode][$cityId];
    }

    /**
     * @param string $number
     * @return NumberRange
     */
    public static function getByNumber($number)
    {
        return NumberRange::find()
            ->andWhere(['is_active' => true])
            ->andWhere(['<=', 'full_number_from', $number])
            ->andWhere(['>=', 'full_number_to', $number])
            ->orderBy(new Expression('ndc IS NOT NULL DESC'))// чтобы большой диапазон по всей стране типа 0000-9999 был в конце
            ->one();
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

        $nnpNumberRange = self::getByNumber($numberModel->number);
        $nnpOperator = $nnpNumberRange ? $nnpNumberRange->operator : null;
        $nnpCountry = $nnpNumberRange ? $nnpNumberRange->country : null;
        $nnpRegion = $nnpNumberRange ? $nnpNumberRange->region : null;
        $nnpCity = $nnpNumberRange ? $nnpNumberRange->city : null;

        $country = $numberModel->country;
        $regionModel = $numberModel->regionModel;
        $city = $numberModel->city;

        if ($nnpCountry) {
            $countryName = ($nnpCountry->code == \app\modules\nnp\models\Country::RUSSIA) ? $nnpCountry->name_rus : $nnpCountry->name_eng;
        } elseif ($country) {
            $countryName = ($country->code == \app\models\Country::RUSSIA) ? $country->name_rus : $country->name;
        } else {
            $countryName = '';
        }

        return [
            'ndc' => $nnpNumberRange ? $nnpNumberRange->ndc_str : (string)$numberModel->ndc,
            'ndc_type' => (int)$numberModel->ndc_type_id,
            'country_name' => $countryName,
            'country_prefix' => $nnpCountry ? $nnpCountry->prefix : null,
            'region_name' => $nnpRegion ? $nnpRegion->name : ($regionModel ? $regionModel->name : null),
            'city_name' => $nnpCity ? $nnpCity->name : ($city ? $city->name : null),
            'operator_name' => $nnpOperator ? $nnpOperator->name : null,
            'number_length' => $city ? (int)$city->postfix_length : null,
        ];
    }
}
