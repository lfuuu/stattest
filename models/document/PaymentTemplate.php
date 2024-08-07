<?php

namespace app\models\document;

use app\classes\helpers\ArrayHelper;
use app\classes\model\ActiveRecord;
use app\helpers\DateTimeZoneHelper;
use app\models\Country;
use app\models\User;
use app\modules\uu\models\Tariff;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "payment_template".
 *
 * @property integer $id
 * @property integer $type_id
 * @property integer $country_code
 * @property integer $version
 * @property integer $is_active
 * @property integer $is_default
 * @property string $content
 * @property string $created_at
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @property Country $country
 * @property PaymentTemplateType $type
 * @property User $updatedBy
 */
class PaymentTemplate extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_template';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                [
                    // Установить "когда создал" и "когда обновил"
                    'class' => TimestampBehavior::class,
                    'value' => new Expression('UTC_TIMESTAMP()'), // "NOW() AT TIME ZONE 'utc'" (PostgreSQL) или 'UTC_TIMESTAMP()' (MySQL)
                ],
                [
                    // Установить "кто создал" и "кто обновил"
                    'class' => AttributeBehavior::class,
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_UPDATE => 'updated_by',
                    ],
                    'value' => Yii::$app->user->getId(),
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'country_code', 'version', 'is_active', 'is_default', 'content', 'created_at'], 'required'],
            [['type_id', 'country_code', 'version', 'updated_by'], 'integer'],
            [['content'], 'string'],
            [['created_at', 'updated_at', 'is_active', 'is_default'], 'safe'],
            [['country_code'], 'exist', 'skipOnError' => true, 'targetClass' => Country::class, 'targetAttribute' => ['country_code' => 'code']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentTemplateType::class, 'targetAttribute' => ['type_id' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Тип расчетного документа',
            'country_code' => 'Код страны',
            'version' => 'Версия',
            'is_active' => 'Исользуется',
            'is_default' => 'По умочанию',
            'content' => 'Содержимое',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
            'updated_by' => 'Изменён',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(PaymentTemplateType::class, ['id' => 'type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        array_map(
            fn(Tariff $tariff) => Tariff::deleteCacheById($tariff->id),
            Tariff::find()->where(['payment_template_type_id' => $this->type_id])->all()
        );
    }

    /**
     * Получить список значений для всех версий
     *
     * @return array
     */
    public function getAllVersionList()
    {
        $versions = ArrayHelper::map(
            self::getAllByTypeIdAndCountryCode($this->type_id, $this->country_code)->all(),
            'id',
            function (PaymentTemplate $model) {
                $updated = $model->updated_at ? : $model->created_at;
                $default = $model->is_default ? ' *' : '';

                return sprintf('v.%d от %s%s', $model->version, DateTimeZoneHelper::getDateTime($updated), $default);
            }
        );
        $versions['0'] = 'Новая версия';

        return $versions;
    }

    /**
     * Получить последнюю версию
     *
     * @param int $typeId
     * @param int $countryCode
     * @return array|self|null
     */
    public static function getLastVersionByTypeIdAndCountryCode($typeId, $countryCode)
    {
        return
            self::find()
                ->where([
                    'type_id' => $typeId,
                    'country_code' => $countryCode,
                ])
                ->orderBy(['version' => SORT_DESC])
                ->one();
    }

    /**
     * Получить дефолтный шаблон
     *
     * @param int $typeId
     * @param int $countryCode
     * @return array|self|null
     */
    public static function getDefaultByTypeIdAndCountryCode($typeId, $countryCode)
    {
        return
            self::getAllByTypeIdAndCountryCode($typeId, $countryCode)
                ->andWhere([
                    'is_default' => 1,
                ])
                ->one();
    }

    /**
     * Получить все для типа и страны
     *
     * @param $typeId
     * @param $countryCode
     * @return \yii\db\ActiveQuery
     */
    public static function getAllByTypeIdAndCountryCode($typeId, $countryCode)
    {
        return
            self::find()
                ->andWhere([
                    'type_id' => $typeId,
                    'country_code' => $countryCode,
                ]);
    }
}
