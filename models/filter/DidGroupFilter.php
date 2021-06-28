<?php

namespace app\models\filter;

use app\classes\traits\GetListTrait;
use app\models\DidGroup;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для DidGroup
 */
class DidGroupFilter extends DidGroup
{
    public $id = '';
    public $country_code = '';
    public $city_id = '';
    public $name = '';
    public $beauty_level = '';
    public $ndc_type_id = '';
    public $is_service = '';

    /**
     * Правила
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['country_code'], 'integer'],
            [['city_id'], 'integer'],
            [['name'], 'string'],
            [['beauty_level'], 'integer'],
            [['ndc_type_id', 'is_service'], 'integer'],
        ];
    }


    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = DidGroup::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $didGroupTableName = DidGroup::tableName();

        $this->id !== '' && $query->andWhere([$didGroupTableName . '.id' => $this->id]);
        $this->country_code !== '' && $query->andWhere([$didGroupTableName . '.country_code' => $this->country_code]);

        switch ($this->city_id) {
            case '':
                break;

            case GetListTrait::$isNull:
                $query->andWhere(['city_id' => null]);
                break;

            case GetListTrait::$isNotNull:
                $query->andWhere(['IS NOT', 'city_id', null]);
                break;

            default:
                $query->andWhere([$didGroupTableName . '.city_id' => $this->city_id]);
                break;
        }

        $this->name !== '' && $query->andWhere(['LIKE', $didGroupTableName . '.name', $this->name]);
        $this->beauty_level !== '' && $query->andWhere([$didGroupTableName . '.beauty_level' => $this->beauty_level]);
        $this->ndc_type_id !== '' && $query->andWhere([$didGroupTableName . '.ndc_type_id' => $this->ndc_type_id]);
        $this->is_service !== '' && $query->andWhere(['is_service' => $this->is_service]);

        return $dataProvider;
    }
}
