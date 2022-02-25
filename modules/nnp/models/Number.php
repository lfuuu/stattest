<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\exceptions\ModelValidationException;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * @property int $full_number bigint
 * @property int $country_code
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
 * @property-read Country $country
 * @property-read Operator $operator
 * @property-read Region $region
 * @property-read City $city
 */
class Number extends ActiveRecord
{
    const MCNTELECOM_OPERATOR_ID = 6720;
    const MCNTELECOM_OPERATOR_SOURCE = 'MSNTELECOM';

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'full_number' => 'Полный номер',
            'country_code' => 'Страна',

            'operator_source' => 'Исходный оператор',
            'operator_id' => 'Оператор',

            'region_source' => 'Исходный регион',
            'region_id' => 'Регион',

            'city_source' => 'Исходный город',
            'city_id' => 'Город',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp_ported.number';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_id', 'region_id', 'city_id'], 'integer'],
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
     * @throws \yii\base\InvalidParamException
     */
    public function getUrl()
    {
        return self::getUrlById($this->full_number);
    }

    /**
     * @param int $full_number
     * @return string
     * @throws \yii\base\InvalidParamException
     */
    public static function getUrlById($full_number)
    {
        return Url::to(['/nnp/number/edit', 'full_number' => $full_number]);
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

    public static function forcePorting($number, $isWithSync = true)
    {
        if (
            strpos((string)$number, '79') !== 0
            || !\app\models\Number::find()->where(['number' => $number])->exists()
        ) {
            throw new \InvalidArgumentException('Неправильный номер');
        }

        $nnpNumber = self::findOne(['full_number' => $number]);

        if ($nnpNumber && $nnpNumber->operator_id == self::MCNTELECOM_OPERATOR_ID){
            throw new \InvalidArgumentException('Номер уже портирован в МСН');
        }

        if (!$nnpNumber) {
            $nnpNumber = new self;
            $nnpNumber->full_number = $number;
            $nnpNumber->country_code = \app\models\Country::RUSSIA;
        }

        $nnpNumber->operator_id = self::MCNTELECOM_OPERATOR_ID;
        $nnpNumber->operator_source = self::MCNTELECOM_OPERATOR_SOURCE;

        if (!$nnpNumber->save()) {
            throw new ModelValidationException($nnpNumber);
        }

        if ($isWithSync) {
            self::notifySync();
        }
    }

    public static function notifySync()
    {
        \Yii::$app->dbPg->createCommand("select event.notify_event_to_all('nnp_ported_number')")->execute();
    }
}
