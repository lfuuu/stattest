<?php

namespace app\modules\uu\models;

use app\classes\helpers\DependecyHelper;
use app\classes\Html;
use app\classes\model\ActiveRecord;
use Yii;
use yii\caching\ChainedDependency;
use yii\caching\DbDependency;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Связанность тарифа услуги с тарифами пакетов
 *
 * @property integer $id
 * @property integer $tariff_id
 * @property integer $package_tariff_id
 *
 * @property-read Tariff $tariff
 * @property-read Tariff $packagesTariffs
 *
 * @method static TariffPeriod findOne($condition)
 * @method static TariffPeriod[] findAll($condition)
 */
class TariffBundle extends ActiveRecord
{
    protected $isAttributeTypecastBehavior = true;

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'uu_tariff_bundle';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                \app\classes\behaviors\HistoryChanges::class,
            ]
        );

    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['tariff_id', 'package_tariff_id'], 'required'],
            [['tariff_id', 'package_tariff_id'], 'number'],
        ];
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
    public function getPackagesTariffs()
    {
        return $this->hasMany(Tariff::class, ['id' => 'package_tariff_id']);
    }
}
