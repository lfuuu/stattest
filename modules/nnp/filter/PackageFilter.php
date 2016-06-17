<?php

namespace app\modules\nnp\filter;

use app\classes\traits\GetListTrait;
use app\modules\nnp\models\Package;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Package
 */
class PackageFilter extends Package
{
    public $name = '';
    public $tariff_id = '';
    public $period_id = '';
    public $package_type_id = '';
    public $destination_id = '';
    public $pricelist_id = '';

    public $price_from = '';
    public $price_to = '';

    public $minute_from = '';
    public $minute_to = '';

    public function rules()
    {
        return [
            [['name'], 'string'],
            [['tariff_id', 'period_id', 'package_type_id', 'destination_id', 'pricelist_id'], 'integer'],
            [['price_from', 'price_to'], 'integer'],
            [['minute_from', 'minute_to'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Package::find();
        $packageTableName = Package::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->name && $query->andWhere(['LIKE', $packageTableName . '.name', $this->name]);
        $this->tariff_id && $query->andWhere([$packageTableName . '.tariff_id' => $this->tariff_id]);
        $this->period_id && $query->andWhere([$packageTableName . '.period_id' => $this->period_id]);
        $this->package_type_id && $query->andWhere([$packageTableName . '.package_type_id' => $this->package_type_id]);
        $this->destination_id && $query->andWhere([$packageTableName . '.destination_id' => $this->destination_id]);

        $this->price_from !== '' && $query->andWhere(['>=', $packageTableName . '.price', $this->price_from]);
        $this->price_to !== '' && $query->andWhere(['<=', $packageTableName . '.price', $this->price_to]);

        $this->minute_from !== '' && $query->andWhere(['>=', $packageTableName . '.minute', $this->minute_from]);
        $this->minute_to !== '' && $query->andWhere(['<=', $packageTableName . '.minute', $this->minute_to]);

        switch ($this->pricelist_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($packageTableName . '.pricelist_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($packageTableName . '.pricelist_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$packageTableName . '.pricelist_id' => $this->pricelist_id]);
                break;
        }

        return $dataProvider;
    }
}
