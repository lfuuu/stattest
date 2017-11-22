<?php

namespace app\modules\nnp\filters;

use app\classes\traits\GetListTrait;
use app\modules\nnp\models\Number;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Number
 */
class NumberFilter extends Number
{
    public $full_number = '';
    public $country_code = '';

    public $operator_source = '';
    public $operator_id = '';

    public $region_source = '';
    public $region_id = '';

    public $city_source = '';
    public $city_id = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['operator_source', 'region_source', 'city_source', 'full_number'], 'string'],
            [['country_code', 'operator_id', 'region_id', 'city_id'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Number::find();
        $numberTableName = Number::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->full_number && $query->andWhere([$numberTableName . '.full_number' => $this->full_number]);
        $this->country_code && $query->andWhere([$numberTableName . '.country_code' => $this->country_code]);

        switch ($this->operator_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberTableName . '.operator_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberTableName . '.operator_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberTableName . '.operator_id' => $this->operator_id]);
                break;
        }

        switch ($this->region_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberTableName . '.region_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberTableName . '.region_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberTableName . '.region_id' => $this->region_id]);
                break;
        }

        switch ($this->city_id) {
            case '':
                break;
            case GetListTrait::$isNull:
                $query->andWhere($numberTableName . '.city_id IS NULL');
                break;
            case GetListTrait::$isNotNull:
                $query->andWhere($numberTableName . '.city_id IS NOT NULL');
                break;
            default:
                $query->andWhere([$numberTableName . '.city_id' => $this->city_id]);
                break;
        }

        $this->operator_source && $query->andWhere(['LIKE', $numberTableName . '.operator_source', $this->operator_source]);
        $this->region_source && $query->andWhere(['LIKE', $numberTableName . '.region_source', $this->region_source]);
        $this->city_source && $query->andWhere(['LIKE', $numberTableName . '.city_source', $this->city_source]);

        return $dataProvider;
    }
}
