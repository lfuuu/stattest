<?php

namespace app\models\filter;

use app\models\UsageVoip;
use yii\data\ActiveDataProvider;

class UsageVoipFilter extends UsageVoip
{
    public $id = '';

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $usageVoipTableName = UsageVoip::tableName();

        $query = UsageVoip::find()
            ->joinWith('regionName')
            ->with('regionName')
        ;
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->id !== '' && $query->andWhere(["{$usageVoipTableName}.id" => $this->id]);

        return $dataProvider;
    }
}