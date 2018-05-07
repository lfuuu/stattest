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
    public $term_trunk_id = '';
    public $orig_trunk_id = '';
    public $is_active = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'term_trunk_id', 'orig_trunk_id', 'is_active'], 'integer'],
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
        $this->term_trunk_id !== '' && $query->andWhere([$imsiPartnerTableName . '.term_trunk_id' => $this->term_trunk_id]);
        $this->orig_trunk_id !== '' && $query->andWhere([$imsiPartnerTableName . '.orig_trunk_id' => $this->orig_trunk_id]);
        $this->is_active !== '' && $query->andWhere([$imsiPartnerTableName . '.is_active' => $this->is_active]);

        return $dataProvider;
    }
}
