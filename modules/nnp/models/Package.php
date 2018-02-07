<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\Tariff;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\Url;

/**
 * Пакеты
 *
 * @property int $tariff_id
 * @property int $service_type_id
 * @property int $is_termination 0 (по умолчанию) - плата за исходящие звонкие, 1 (обычно только для freephone) - плата за входящие звонки
 * @property int $tarification_free_seconds
 * @property int $tarification_interval_seconds
 * @property int $tarification_type
 * @property int $tarification_min_paid_seconds
 * @property int $currency_id
 * @property bool $is_include_vat
 * @property string $name
 *
 * @property-read Tariff $tariff  FK нет, ибо в таблица в другой БД
 * @property-read PackageMinute[] $packageMinutes
 * @property-read PackagePrice[] $packagePrices
 * @property-read PackagePricelist[] $packagePricelists
 */
class Package extends ActiveRecord
{
    const TARIFICATION_TYPE_ROUND = 1;
    const TARIFICATION_TYPE_CEIL = 2;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'tariff_id' => 'Тариф',
            'service_type_id' => 'Тип услуги',
            'is_termination' => 'Плата за входящие',
            'tarification_free_seconds' => 'Бесплатно, сек.',
            'tarification_interval_seconds' => 'Интервал билингования, сек.',
            'tarification_type' => 'Тип округления',
            'tarification_min_paid_seconds' => 'Минимальная плата, сек.',
            'currency_id' => 'Валюта',
            'is_include_vat' => 'Включая НДС', // дубль из Tariff
            'name' => 'Название', // дубль из Tariff
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.package';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id'], 'required'],
            [['is_termination', 'is_include_vat'], 'boolean'],
            [
                [
                    'tariff_id',
                    'tarification_free_seconds',
                    'tarification_interval_seconds',
                    'tarification_type',
                    'tarification_min_paid_seconds',
                ],
                'integer'
            ],
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
    public function __toString()
    {
        return $this->tariff->name;
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::className(), ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackageMinutes()
    {
        return $this->hasMany(PackageMinute::className(), ['tariff_id' => 'tariff_id'])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePrices()
    {
        return $this->hasMany(PackagePrice::className(), ['tariff_id' => 'tariff_id'])
            ->orderBy(['weight' => SORT_DESC])
            ->indexBy('id');
    }

    /**
     * @return ActiveQuery
     */
    public function getPackagePricelists()
    {
        return $this->hasMany(PackagePricelist::className(), ['tariff_id' => 'tariff_id'])
            ->indexBy('id');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return self::getUrlById($this->tariff_id);
    }

    /**
     * @param int $tariffId
     * @return string
     */
    public static function getUrlById($tariffId)
    {
        return Url::to(['/nnp/package/edit', 'tariff_id' => $tariffId]);
    }
}