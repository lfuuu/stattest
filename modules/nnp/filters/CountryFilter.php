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
    public $name_rus = '';
    public $alpha_3 = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['code'], 'integer'],
            [['name', 'name_rus', 'alpha_3'], 'string'],
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
        $this->name && $query->andWhere(['LIKE', $cityTableName . '.name', $this->name]);
        $this->name_rus && $query->andWhere(['LIKE', $cityTableName . '.name_rus', $this->name_rus]);
        $this->alpha_3 && $query->andWhere([$cityTableName . '.alpha_3' => $this->alpha_3]);

        return $dataProvider;
    }
}
