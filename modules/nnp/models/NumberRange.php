<?php

namespace app\modules\nnp\models;

use app\classes\Connection;
use app\models\billing\InstanceSettings;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $country_code
 * @property int $ndc
 * @property int $number_from bigint
 * @property int $number_to bigint
 * @property int $full_number_from bigint
 * @property int $full_number_to bigint
 * @property string $operator_source
 * @property int $operator_id
 * @property string $region_source
 * @property string $city_source
 * @property int $region_id
 * @property int $city_id // индекса и FK нет, потому что таблица городов в другой БД
 * @property bool $is_active
 * @property string $ndc_type_source
 * @property int $ndc_type_id
 * @property string $date_stop date
 * @property string $date_resolution date
 * @property string $detail_resolution
 * @property string $status_number
 *
 * @property City $city
 * @property Operator $operator
 * @property Region $region
 * @property NumberRangePrefix[] $numberRangePrefixes
 * @property NdcType $ndcType
 * @property Country $country
 */
class NumberRange extends ActiveRecord
{
    // Методы для полей insert_time, insert_user_id, update_time, update_user_id
    use \app\classes\traits\InsertUpdateUserTrait;

    const DEFAULT_MOSCOW_NDC = 495;

    private static $_triggerTables = [
        // 'nnp.account_tariff_light',
        'nnp.country',
        'nnp.destination',
        'nnp.number_range',
        'nnp.number_range_prefix',
        'nnp.operator',
        // 'nnp.package',
        // 'nnp.package_minute',
        // 'nnp.package_price',
        // 'nnp.package_pricelist',
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
            'number_from' => 'Номер от',
            'number_to' => 'Номер до',
            'full_number_from' => 'Полный номер от',
            'full_number_to' => 'Полный номер до',
            'operator_source' => 'Исходный оператор',
            'operator_id' => 'Оператор',
            'region_source' => 'Исходный регион',
            'city_source' => 'Исходный город',
            'region_id' => 'Регион',
            'city_id' => 'Город',
            'is_active' => 'Вкл.',
            'ndc_type_source' => 'Исходный тип NDC',
            'ndc_type_id' => 'Тип NDC',
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
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOperator()
    {
        return $this->hasOne(Operator::className(), ['id' => 'operator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNdcType()
    {
        return $this->hasOne(NdcType::className(), ['id' => 'ndc_type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['code' => 'country_code']);
    }

    /**
     * @return ActiveQuery
     */
    public function getNumberRangePrefixes()
    {
        return $this->hasMany(NumberRangePrefix::className(), ['number_range_id' => 'id']);
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
        $sql = 'select from event.notify_nnp_all(:p_server_id)';
        $activeQuery = InstanceSettings::find()
            ->where(['active' => true]);
        foreach ($activeQuery->each() as $instanceSettings) {
            $db->createCommand($sql, [':p_server_id' => $instanceSettings->id])->execute();
        }
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
                ->select('ndc')
                ->distinct()
                ->where($where)
                ->indexBy('ndc')
                ->orderBy(['ndc' => SORT_ASC])
                ->column();
        }

        return $_cache[$countryCode][$cityId];
    }
}
