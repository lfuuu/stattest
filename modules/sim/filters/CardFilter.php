<?php

namespace app\modules\sim\filters;

use app\modules\sim\models\Card;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Card
 */
class CardFilter extends Card
{
    public $iccid = '';
    public $imei = '';
    public $client_account_id = '';
    public $is_active = '';
    public $status_id = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['iccid', 'imei', 'client_account_id', 'is_active', 'status_id'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Card::find();
        $cardTableName = Card::tableName();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->iccid && $query->andWhere([$cardTableName . '.iccid' => $this->iccid]);
        $this->imei && $query->andWhere([$cardTableName . '.imei' => $this->imei]);
        $this->client_account_id && $query->andWhere([$cardTableName . '.client_account_id' => $this->client_account_id]);
        $this->is_active && $query->andWhere([$cardTableName . '.is_active' => $this->is_active]);
        $this->status_id && $query->andWhere([$cardTableName . '.status_id' => $this->status_id]);

        return $dataProvider;
    }
}
