<?php

namespace app\modules\sim\filters;

use app\modules\sim\models\Card;
use app\modules\sim\models\Imsi;
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

    public $imsi = '';
    public $msisdn = '';
    public $did = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['iccid', 'imei', 'client_account_id', 'is_active', 'status_id'], 'integer'],
            [['imsi', 'msisdn', 'did'], 'integer'],
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = Card::find()
            ->with('imsies');

        $cardTableName = Card::tableName();
        $imsiTableName = Imsi::tableName();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->iccid && $query->andWhere([$cardTableName . '.iccid' => $this->iccid]);
        $this->imei && $query->andWhere([$cardTableName . '.imei' => $this->imei]);
        $this->client_account_id && $query->andWhere([$cardTableName . '.client_account_id' => $this->client_account_id]);
        $this->is_active && $query->andWhere([$cardTableName . '.is_active' => $this->is_active]);
        $this->status_id && $query->andWhere([$cardTableName . '.status_id' => $this->status_id]);

        if ($this->imsi || $this->msisdn || $this->did) {
            $query->joinWith('imsies');
        }

        $this->imsi && $query->andWhere([$imsiTableName . '.imsi' => $this->imsi]);
        $this->msisdn && $query->andWhere([$imsiTableName . '.msisdn' => $this->msisdn]);
        $this->did && $query->andWhere([$imsiTableName . '.did' => $this->did]);

        return $dataProvider;
    }
}
