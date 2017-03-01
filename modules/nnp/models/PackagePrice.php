<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use app\classes\uu\model\Tariff;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Пакеты. Цена по направлениям
 *
 * @property int id
 * @property int tariff_id
 * @property int destination_id
 * @property float price
 * @property float interconnect_price
 *
 * @property Tariff tariff  FK нет, ибо в таблица в другой БД
 * @property Package package
 * @property Destination destination
 */
class PackagePrice extends ActiveRecord
{
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
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.package_price';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'destination_id', 'price'], 'required'],
            [['tariff_id', 'destination_id'], 'integer'],
            [['price', 'interconnect_price'], 'number'],
        ];
    }

    /**
     * Returns the database connection
     *
     * @return Connection
     */
    public static function getDb()
    {
        return Yii::$app->dbPgNnp;
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
    public function getPackage()
    {
        return $this->hasOne(Package::className(), ['tariff_id' => 'tariff_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getDestination()
    {
        return $this->hasOne(Destination::className(), ['id' => 'destination_id']);
    }
}