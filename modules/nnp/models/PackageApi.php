<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\modules\uu\models\Tariff;
use Yii;
use yii\db\ActiveQuery;

/**
 * биллинг API. Пакеты.
 *
 * @property int $id
 * @property int $tariff_id
 * @property int $api_pricelist_id
 *
 * @property-read Tariff $tariff  FK нет, ибо в таблица в другой БД
 * @property-read Package $package
 * @property-read Destination $destination
 */
class PackageApi extends ActiveRecord
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
            'api_pricelist_id' => 'Прайслист API',
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.package_api';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'api_pricelist_id'], 'required'],
            [['tariff_id', 'api_pricelist_id'], 'integer'],
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
}