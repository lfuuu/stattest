<?php

namespace app\modules\nnp2\models;

use app\classes\Connection;
use app\classes\model\ActiveRecord;
use app\classes\traits\GetInsertUserTrait;
use app\classes\traits\GetUpdateUserTrait;
use app\modules\nnp\models\Country;
use app\modules\nnp2\classes\NumberRangeMassUpdater;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\Url;
use function _1c\tr;

/**
 * @property int $id
 * @property int $country_code
 * @property string $ndc
 * @property int $region_id
 * @property int $city_id
 * @property int $cnt bigint
 * @property int $parent_id
 * @property boolean $is_valid
 *
 * @property-read Country $country
 * @property-read Region $region
 * @property-read City $city
 * @property-read GeoPlace $parent
 * @property-read GeoPlace[] $childs

 */
class GeoPlace extends ActiveRecord
{
    // Определяет getList (список для selectbox)
    use \app\classes\traits\GetListTrait {
        getList as getListTrait;
    }

    use GetInsertUserTrait;
    use GetUpdateUserTrait;

    const MIN_CNT = 0;

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
            'region_id' => 'Регион',
            'city_id' => 'Город',
            'cnt' => 'Кол-во номеров',
            'parent_id' => 'Местоположение-родитель',
            'is_valid' => 'Подтверждён',

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
        return 'nnp2.geo_place';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['country_code', 'region_id', 'city_id', 'parent_id'], 'integer'],
            [['is_valid'], 'boolean'],
            [['ndc'], 'string'],
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
        return Url::to(['/nnp2/geo-place/edit', 'id' => $id]);
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
    public function getCountry()
    {
        return $this->hasOne(Country::class, ['code' => 'country_code']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(self::class, ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChilds()
    {
        return $this->hasMany(self::class, ['parent_id' => 'id']);
    }

    /**
     * @param self $model
     * @return self
     */
    public static function getParentModel(self $model)
    {
        if ($parent = $model->parent) {
            return self::getParentModel($parent);
        }

        return $model;
    }

    /**
     * @return self
     */
    public function getMainParent()
    {
        return self::getParentModel($this);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $parts = [$this->country->name_rus];
        $parts[] = $this->ndc ? : '-';
        $parts[] = $this->region->name ? : '-';
        $parts[] = $this->city->name ? : '-';

        return implode(', ', $parts);
    }

    /**
     * Вернуть список всех доступных значений
     *
     * @param bool|string $isWithEmpty false - без пустого, true - с '----', string - с этим значением
     * @param bool $isWithNullAndNotNull
     * @param int $countryCode
     * @param string $ndc
     * @param int $regionId
     * @param bool $isFormatted
     * @param int $minCnt
     * @return \string[]
     */
    public static function getList(
        $isWithEmpty = false,
        $isWithNullAndNotNull = false,
        $countryCode = null,
        $ndc = '',
        $regionId = null,
        $isFormatted = false,
        $minCnt = self::MIN_CNT
    ) {
        $list = self::find()
            ->with(['country', 'region', 'city'])
            ->where($where = [
                'AND',
                ['is_valid' => 1],
                ['parent_id' => null],
                $countryCode ? ['country_code' => $countryCode] : [],
                [
                    'OR',
                    $ndc ? ['ndc' => $ndc] : [],
                    $regionId ? ['region_id' => $regionId] : [],
                ],
                $minCnt ? ['>=', 'cnt', $minCnt] : []
            ])
            ->orderBy($orderBy = ['region_id' => SORT_ASC])
            ->indexBy($indexBy = 'id')
            ->all();

        if (!$isFormatted) {
            return $list;
        }

        $ready = [];
        /** @var self $line */
        foreach ($list as $key => $line) {
            $ready[$key] = sprintf("%s (%s)", strval($line), $key);
        }

        $ready = self::getEmptyList($isWithEmpty, $isWithNullAndNotNull) + $ready;
        return $ready;
    }

    /**
     * @param null $attributeNames
     * @param bool $clearErrors
     * @return bool
     */
    public function validate($attributeNames = null, $clearErrors = true)
    {
        $validation = parent::validate($attributeNames, $clearErrors);

        if ($validation) {
            if ($this->parent_id) {
                if (!$this->parent->is_valid) {
                    $this->addError('parent_id', 'Родитель не подтверждён');

                    return false;
                }

                return true;
            } elseif ($this->is_valid) {
                if ($this->region && !$this->region->is_valid) {
                    $this->addError('is_valid', 'Регион не подтверждён');

                    return false;
                }

                if ($this->city && !$this->city->is_valid) {
                    $this->addError('is_valid', 'Город не подтверждён');

                    return false;
                }
            }

            if (!$this->is_valid && $this->childs) {
                $this->addError('is_valid', 'Есть синонимы');

                return false;
            }

            return true;
        }

        return false;
    }


    /**
     * @param boolean|true $runValidation
     * @param null|array $attributeNames
     * @return bool
     * @throws \yii\db\Exception
     */
    public function save($runValidation = true, $attributeNames = null)
    {
        /** @var Connection $dbPgNnp */
        $dbPgNnp = self::getDb();
        $transaction = $dbPgNnp->beginTransaction();
        try {

            $oldIsValid = $this->getOldAttribute('is_valid');
            parent::save($runValidation, $attributeNames);
            if ($this->is_valid !== $oldIsValid) {
                NumberRangeMassUpdater::me()->update($this->id);
            }

            if (!$this->parent_id) {
                $this->parent_id = null;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            return sprintf('%s %s', $e->getMessage(), $e->getTraceAsString());
        }
    }
}
