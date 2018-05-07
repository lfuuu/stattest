<?php

namespace app\modules\sim\filters;

use app\models\Number;
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
    public $imsi_partner = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['iccid', 'imei', 'client_account_id', 'is_active', 'status_id'], 'integer'], // card
            [['imsi', 'msisdn', 'did', 'imsi_partner'], 'integer'], // imsi
        ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $cardTableName = Card::tableName();
        $imsiTableName = Imsi::tableName();

        $query = Card::find()
            ->with('imsies')
            ->joinWith('imsies');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->iccid && $query->andWhere([$cardTableName . '.iccid' => $this->iccid]);
        $this->imei && $query->andWhere([$cardTableName . '.imei' => $this->imei]);
        $this->client_account_id && $query->andWhere([$cardTableName . '.client_account_id' => $this->client_account_id]);
        $this->is_active && $query->andWhere([$cardTableName . '.is_active' => $this->is_active]);
        $this->status_id && $query->andWhere([$cardTableName . '.status_id' => $this->status_id]);

        $this->imsi && $query->andWhere([$imsiTableName . '.imsi' => $this->imsi]);
        $this->msisdn && $query->andWhere([$imsiTableName . '.msisdn' => $this->msisdn]);
        $this->did && $query->andWhere([$imsiTableName . '.did' => $this->did]);
        $this->imsi_partner && $query->andWhere([$imsiTableName . '.partner_id' => $this->imsi_partner]);

        return $dataProvider;
    }
}
