<?php

namespace app\models\filter;

use app\models\Country;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Country
 */
class CountryFilter extends Country
{
    public $code = '';
    public $alpha_3 = '';
    public $name = '';
    public $in_use = '';
    public $lang = '';
    public $currency_id = '';
    public $prefix = '';

    public function rules()
    {
        return [
            [['code'], 'string'],
            [['alpha_3'], 'string'],
            [['name'], 'string'],
            [['in_use'], 'integer'],
            [['lang'], 'string'],
            [['currency_id'], 'string'],
            [['prefix'], 'integer'],
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
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->code !== '' && $query->andWhere(['code' => $this->code]);
        $this->alpha_3 !== '' && $query->andWhere(['LIKE', 'alpha_3', $this->alpha_3]);
        $this->name !== '' && $query->andWhere(['LIKE', 'name', $this->name]);
        $this->in_use !== '' && $query->andWhere(['in_use' => $this->in_use]);
        $this->lang !== '' && $query->andWhere(['LIKE', 'lang', $this->lang]);
        $this->currency_id !== '' && $query->andWhere(['currency_id' => $this->currency_id]);
        $this->prefix !== '' && $query->andWhere(['prefix' => $this->prefix]);

        return $dataProvider;
    }
}
