<?php

namespace app\modules\nnp2\models;

use app\classes\model\ActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use app\classes\traits\GetUpdateUserTrait;
use app\models\User;
use app\modules\nnp\models\Country;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * @property int $id
 * @property int $geo_place_id
 * @property int $ndc_type_id
 * @property int $operator_id
 *
 * @property int $number_from bigint
 * @property int $number_to bigint
 *
 * @property int $full_number_from bigint
 * @property int $full_number_to bigint
 *
 * @property int $cnt bigint
 *
 * @property boolean $is_active
 * @property boolean $is_valid
 *
 * @property string $allocation_date_stop date
 * @property string $allocation_date_start date
 * @property string $allocation_reason
 * @property string $comment
 *
 * @property int $previous_id
 *
 * @property int $range_short_id
 * @property int $range_short_old_id
 *
 * @property string $stop_time
 * @property integer $stop_user_id
 *
 * @property-read GeoPlace $geoPlace
 * @property-read Country $country
 * @property-read Region $region
 * @property-read City $city
 * @property-read NdcType $ndcType
 * @property-read Operator $operator
 * @property-read NumberRange $previous
 * @property-read RangeShort $rangeShort
 * @property-read RangeShort $rangeShortOld
 * @property-read User $stopUser
 */
class NumberRange extends ActiveRecord
{
    use GetInsertUserTrait;
    use GetUpdateUserTrait;

    const DEFAULT_MOSCOW_NDC = 495;

    private static $_triggerTables = [
        'nnp.country',
        'nnp.number_range',
        'nnp.operator',
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

            'geo_place_id' => 'Гео',
            'ndc_type_id' => 'Тип NDC',
            'operator_id' => 'Оператор',

            'number_from' => 'Номер от',
            'number_to' => 'Номер до',

            'full_number_from' => 'Полный номер от',
            'full_number_to' => 'Полный номер до',

            'cnt' => 'Кол-во номеров',

            'is_active' => 'Вкл.',
            'is_valid' => 'Подтверждён',

            'allocation_reason' => 'Причина выделения',
            'allocation_date_start' => 'Дата выделения диапазона',
            'allocation_date_stop' => 'Дата выключения',
            'comment' => 'Комментарий',

            'previous_id' => 'Предыдущий диапазон',

            'range_short_id' => 'Краткая информация по диапазону',
            'range_short_old_id' => 'Предыдущая краткая информация по диапазону',

            'insert_time' => 'Когда создал',
            'insert_user_id' => 'Кто создал',
            'update_time' => 'Когда редактировал',
            'update_user_id' => 'Кто редактировал',

            'stop_time' => 'Когда выключен',
            'stop_user_id' => 'Кто выключел',
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
        return 'nnp2.number_range';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['is_active', 'is_valid'], 'boolean'],
            [['geo_place_id', 'ndc_type_id', 'operator_id', 'range_short_old_id', 'range_short_id'], 'integer'],
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
        return Url::to(['/nnp2/number-range/edit', 'id' => $id]);
    }

    /**
     * @return ActiveQuery
     */
    public function getGeoPlace()
    {
        return $this->hasOne(GeoPlace::class, ['id' => 'geo_place_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCountry()
    {
        return $this->geoPlace->getCountry();
    }

    /**
     * @return string
     */
    public function getNdc()
    {
        return $this->geoPlace->ndc;
    }

    /**
     * @return ActiveQuery
     */
    public function getRegion()
    {
        return $this->geoPlace->getRegion();
    }

    /**
     * @return ActiveQuery
     */
    public function getCity()
    {
        return $this->geoPlace->getCity();
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
    public function getOperator()
    {
        return $this->hasOne(Operator::class, ['id' => 'operator_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPrevious()
    {
        return $this->hasOne(NumberRange::class, ['id' => 'previous_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRangeShort()
    {
        return $this->hasOne(RangeShort::class, ['id' => 'range_short_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRangeShortOld()
    {
        return $this->hasOne(RangeShort::class, ['id' => 'range_short_old_id']);
    }
}
