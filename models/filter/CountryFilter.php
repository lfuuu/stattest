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
    public $name_rus = '';
    public $name_rus_full = '';
    public $in_use = '';
    public $lang = '';
    public $currency_id = '';
    public $prefix = '';
    public $site = '';

    /**
     * @return array
     */
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
            [['site'], 'string'],
            [['name_rus'], 'string'],
            [['name_rus_full'], 'string'],
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
        $this->name_rus !== '' && $query->andWhere(['LIKE', 'name_rus', $this->name_rus]);
        $this->name_rus_full !== '' && $query->andWhere(['LIKE', 'name_rus_full', $this->name_rus_full]);
        $this->in_use !== '' && $query->andWhere(['in_use' => $this->in_use]);
        $this->lang !== '' && $query->andWhere(['LIKE', 'lang', $this->lang]);
        $this->currency_id !== '' && $query->andWhere(['currency_id' => $this->currency_id]);
        $this->prefix !== '' && $query->andWhere(['prefix' => $this->prefix]);
        $this->site !== '' && $query->andWhere(['LIKE', 'site', $this->site]);

        $query->orderBy(['order' => SORT_ASC]);

        return $dataProvider;
    }
}
