<?php

namespace app\modules\sim\filters;

use app\modules\sim\models\CardStatus;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для CardStatus
 */
class CardStatusFilter extends CardStatus
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
        $query = CardStatus::find();
        $cardStatusTableName = CardStatus::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id && $query->andWhere([$cardStatusTableName . '.id' => $this->id]);
        $this->name && $query->andWhere(['LIKE', $cardStatusTableName . '.name', $this->name]);

        return $dataProvider;
    }
}
