<?php

namespace app\modules\nnp\filters;

use app\modules\nnp\models\Land;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Land
 */
class LandFilter extends Land
{
    public $id = '';
    public $name = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'string'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Land::find();
        $landTableName = Land::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id && $query->andWhere([$landTableName . '.id' => $this->id]);
        $this->name && $query->andWhere(['LIKE', $landTableName . '.name', $this->name]);

        return $dataProvider;
    }
}
