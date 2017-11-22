<?php

namespace app\modules\nnp\filters;

use app\modules\nnp\models\Status;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Status
 */
class StatusFilter extends Status
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
        $query = Status::find();
        $statusTableName = Status::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id && $query->andWhere([$statusTableName . '.id' => $this->id]);
        $this->name && $query->andWhere(['LIKE', $statusTableName . '.name', $this->name]);

        return $dataProvider;
    }
}
