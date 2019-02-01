<?php

namespace app\modules\nnp\filters;

use app\modules\nnp\models\Country;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Country
 */
class CountryFilter extends Country
{
    public $code = '';
    public $name = '';
    public $prefix = '';
    public $is_open_numbering_plan = '';
    public $use_weak_matching = '';
    public $default_operator = '';
    public $default_type_ndc = '';
    public $name_rus = '';
    public $name_eng = '';
    public $alpha_3 = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['code', 'is_open_numbering_plan', 'use_weak_matching', 'default_operator', 'default_type_ndc'], 'integer'],
            [['name', 'name_rus', 'name_eng', 'alpha_3', 'prefix'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Country::find();
        $cityTableName = Country::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->code && $query->andWhere([$cityTableName . '.code' => $this->code]);
        $this->prefix && $query->andWhere([$cityTableName . '.prefix' => $this->prefix]);
        $this->default_operator && $query->andWhere([$cityTableName . '.default_operator' => $this->default_operator]);
        $this->default_type_ndc && $query->andWhere([$cityTableName . '.default_type_ndc' => $this->default_type_ndc]);
        $this->is_open_numbering_plan && $query->andWhere([$cityTableName . '.is_open_numbering_plan' => $this->is_open_numbering_plan]);
        $this->use_weak_matching && $query->andWhere([$cityTableName . '.use_weak_matching' => $this->use_weak_matching]);
        $this->name && $query->andWhere(['LIKE', $cityTableName . '.name', $this->name]);
        $this->name_rus && $query->andWhere(['LIKE', $cityTableName . '.name_rus', $this->name_rus]);
        $this->name_eng && $query->andWhere(['LIKE', $cityTableName . '.name_eng', $this->name_eng]);
        $this->alpha_3 && $query->andWhere([$cityTableName . '.alpha_3' => $this->alpha_3]);

        return $dataProvider;
    }
}
