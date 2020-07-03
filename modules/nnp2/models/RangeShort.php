<?php

namespace app\modules\nnp2\models;

use app\classes\DateTimeWithUserTimezone;
use app\classes\model\ActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use app\classes\traits\GetUpdateUserTrait;
use app\helpers\DateTimeZoneHelper;
use app\modules\nnp\models\Country;
use DateTimeZone;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $country_code
 * @property string $ndc
 * @property int $ndc_type_id
 * @property int $region_id
 * @property int $city_id
 * @property int $operator_id
 *
 * @property int $number_from bigint
 * @property int $number_to bigint
 *
 * @property int $full_number_from bigint
 * @property int $full_number_to bigint
 *
 * @property string $allocation_date_start date
 *
 * @property-read Country $country
 * @property-read Region $region
 * @property-read City $city
 * @property-read NdcType $ndcType
 * @property-read Operator $operator
 */
class RangeShort extends ActiveRecord
{
    use GetInsertUserTrait;
    use GetUpdateUserTrait;

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
            'ndc_type_id' => 'Тип NDC',
            'region_id' => 'Регион',
            'city_id' => 'Город',
            'operator_id' => 'Оператор',

            'number_from' => 'Номер от',
            'number_to' => 'Номер до',

            'full_number_from' => 'Полный номер от',
            'full_number_to' => 'Полный номер до',

            'allocation_date_start' => 'Дата выделения диапазона',

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
        return 'nnp2.range_short';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['ndc'], 'string'],
            [['country_code', 'ndc_type_id', 'region_id', 'city_id', 'operator_id', 'number_from', 'number_to'], 'integer'],
            [['allocation_date_start'], 'string'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp2;
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
        return Url::to(['/nnp2/range-short/view', 'id' => $id]);
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
    public function getNdcType()
    {
        return $this->hasOne(NdcType::class, ['id' => 'ndc_type_id']);
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
    public function getOperator()
    {
        return $this->hasOne(Operator::class, ['id' => 'operator_id']);
    }

    public function getLastLogDate()
    {
        /** @var RangeShortLog $lastLog */
        $lastLog =
            RangeShortLog::find()
                ->andWhere(['event' => RangeShortLog::EVENT_FINISH])
                ->orderBy('id desc')
                ->limit(1)
                ->one();

        $dateStr = '-';
        if ($lastLog) {
            $dateStr = (new DateTimeWithUserTimezone($lastLog->inserted_at,
                new DateTimeZone(DateTimeZoneHelper::TIMEZONE_UTC)))->format('Y-m-d H:i:s');
        }

        return $dateStr;
    }
}
