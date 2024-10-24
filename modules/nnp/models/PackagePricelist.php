<?php

namespace app\modules\nnp\models;

use app\classes\model\ActiveRecord;
use app\models\billing\Pricelist;
use app\modules\nnp;
use app\modules\uu\models\Tariff;
use Yii;
use yii\db\ActiveQuery;

/**
 * Пакеты. Прайслист
 *
 * @property int $id
 * @property int $tariff_id
 * @property int $pricelist_id
 * @property int $minute
  *
 * @property-read Tariff $tariff FK нет, ибо в таблица в другой БД
 * @property-read Package $package
 * @property-read Pricelist $pricelist FK нет, ибо в таблица в другой БД
 * @property-read nnp\models\Pricelist $pricelistNnp FK нет, ибо в таблица в другой БД
 */
class PackagePricelist extends ActiveRecord
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
            'pricelist_id' => 'Прайслист',
            'nnp_pricelist_id' => 'Прайслист v.2',
            'minute' => 'Кол-во минут',
        ];
    }

    public function behaviors()
    {
        return [
            'HistoryChanges' => \app\classes\behaviors\HistoryChanges::class,
        ];
    }

    /**
     * Имя таблицы
     *
     * @return string
     */
    public static function tableName()
    {
        return 'billing_uu.package_pricelist';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'pricelist_id', 'minute'], 'required'],
            [['tariff_id', 'pricelist_id', 'minute'], 'integer'],
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
    public function getPricelist()
    {
        return $this->hasOne(Pricelist::class, ['id' => 'pricelist_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPricelistNnp()
    {
        return $this->hasOne(nnp\models\Pricelist::class, ['id' => 'nnp_pricelist_id']);
    }

    /**
     * Вернуть ID родителя
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->tariff_id;
    }

    /**
     * Установить ID родителя
     *
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->tariff_id = $parentId;
    }

    public static function prepareHistoryValue($field, $value)
    {
        switch ($field) {
            case 'pricelist_id':
                if ($pricelist = Pricelist::findOne($value)) {
                    return $pricelist->name;
                }
                break;
        }
        return parent::prepareHistoryValue($field, $value);
    }
}