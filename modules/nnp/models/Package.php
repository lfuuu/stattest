<?php
namespace app\modules\nnp\models;

use app\classes\Connection;
use app\classes\uu\model\Tariff;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Пакеты
 *
 * @property int tariff_id
 *
 * @property Tariff tariff  FK нет, ибо в таблица в другой БД
 * @property PackageMinute[] packageMinutes
 * @property PackagePrice[] packagePrices
 * @property PackagePricelist[] packagePricelists
 */
class Package extends ActiveRecord
{
    /**
     * имена полей
     * @return [] [полеВТаблице => Перевод]
     */
    public function attributeLabels()
    {
        return [
            'tariff_id' => 'Тариф',
        ];
    }

    /**
     * имя таблицы
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
            [['tariff_id'], 'integer'],
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
     * @return string
     */
    public static function getUrlById($tariffId)
    {
        return Url::to(['/nnp/package/edit', 'tariff_id' => $tariffId]);
    }
}