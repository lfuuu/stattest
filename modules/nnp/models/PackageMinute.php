<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use app\classes\uu\model\Tariff;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Пакеты. Предоплаченные минуты
 *
 * @property int id
 * @property int tariff_id
 * @property int destination_id
 * @property int minute
 *
 * @property Tariff tariff  FK нет, ибо в таблица в другой БД
 * @property Package package
 * @property Destination destination
 */
class PackageMinute extends ActiveRecord
{
    /**
     * имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'tariff_id' => 'Тариф',
            'destination_id' => 'Направление',
            'minute' => 'Кол-во минут',
        ];
    }

    /**
     * имя таблицы
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.package_minute';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'minute', 'destination_id'], 'required'],
            [['tariff_id', 'minute', 'destination_id'], 'integer'],
        ];
    }

    /**
     * Returns the database connection
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