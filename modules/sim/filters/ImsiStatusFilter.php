<?php

namespace app\modules\sim\filters;

use app\modules\sim\models\ImsiStatus;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для ImsiStatus
 */
class ImsiStatusFilter extends ImsiStatus
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
        $query = ImsiStatus::find();
        $imsiStatusTableName = ImsiStatus::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id && $query->andWhere([$imsiStatusTableName . '.id' => $this->id]);
        $this->name && $query->andWhere(['LIKE', $imsiStatusTableName . '.name', $this->name]);

        return $dataProvider;
    }
}
