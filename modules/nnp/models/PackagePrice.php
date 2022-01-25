<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\Tariff;
use Yii;
use yii\db\ActiveQuery;

/**
 * Пакеты. Цена по направлениям
 *
 * @property int $id
 * @property int $tariff_id
 * @property int $destination_id
 * @property float $price
 * @property float $interconnect_price
 * @property float $connect_price
 * @property int $weight
 *
 * @property-read Tariff $tariff  FK нет, ибо в таблица в другой БД
 * @property-read Package $package
 * @property-read Destination $destination
 */
class PackagePrice extends ActiveRecord
{
    protected $isAttributeTypecastBehavior = true;

    /**
     * Имена полей
     *
     * @return array [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'tariff_id' => 'Тариф',
            'destination_id' => 'Направление',
            'price' => 'Цена',
            'interconnect_price' => 'Цена интерконнекта',
            'connect_price' => 'Цена коннекта',
            'weight' => 'Вес',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.package_price';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'destination_id', 'price'], 'required'],
            [['tariff_id', 'destination_id', 'weight'], 'integer'],
            [['price', 'interconnect_price', 'connect_price'], 'number'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return \yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPg;
    }

    /**
     * @return ActiveQuery
     */
    public function getTariff()
    {
        return $this->hasOne(Tariff::class, ['id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Package::class, ['tariff_id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDestination()
    {
        return $this->hasOne(Destination::class, ['id' => 'destination_id']);
    }
}