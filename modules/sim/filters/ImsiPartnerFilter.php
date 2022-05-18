<?php

namespace app\modules\sim\filters;

use app\modules\sim\models\ImsiPartner;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для ImsiPartner
 */
class ImsiPartnerFilter extends ImsiPartner
{
    public $id = '';
    public $name = '';
    public $is_active = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'is_active'], 'integer'],
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
        $query = ImsiPartner::find();
        $imsiPartnerTableName = ImsiPartner::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere([$imsiPartnerTableName . '.id' => $this->id]);
        $this->name !== '' && $query->andWhere(['LIKE', $imsiPartnerTableName . '.name', $this->name]);
        $this->is_active !== '' && $query->andWhere([$imsiPartnerTableName . '.is_active' => $this->is_active]);

        return $dataProvider;
    }
}
