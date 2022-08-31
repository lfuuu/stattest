<?php

namespace app\modules\sim\filters;

use app\exceptions\ModelValidationException;
use app\models\ClientAccount;
use app\models\Number;
use app\modules\sim\models\Card;
use app\modules\sim\models\CardStatus;
use app\modules\sim\models\Imsi;
use http\Client;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;

/**
 * Фильтрация для Card
 */
class CardFilter extends Card
{
//    public $iccid = '';
    public $iccid_from = '';
    public $iccid_to = '';
    public $imei = '';
    public $client_account_id = '';
    public $is_active = '';
    public $status_id = '';

    public $imsi = '';
    public $msisdn = '';
    public $did = '';
    public $imsi_partner = '';
    public $profile_id = '';
    public $entry_point_id = '';
    public $region_id = '';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [[/*'iccid', */ 'iccid_from', 'iccid_to', 'imei', 'client_account_id', 'is_active', 'status_id', 'profile_id', 'entry_point_id', 'region_id'], 'integer'], // card
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

        if ($this->iccid_from && !$this->iccid_to) {
//            if (strpos($this->iccid, '*') !== false) {
//                $iccid = strtr($this->iccid, ['**' => '*', '*' => '%']);
//                $query->andWhere($cardTableName . '.iccid::varchar like :like', [':like' => $iccid]);
//            } else {
                $query->andWhere([$cardTableName . '.iccid' => $this->iccid_from]);
//            }
        }

        if ($this->iccid_from && $this->iccid_to) {
            $query->andWhere(['between', $cardTableName . '.iccid' , $this->iccid_from, $this->iccid_to]);
        }

        $this->imei && $query->andWhere([$cardTableName . '.imei' => $this->imei]);
        if ($this->client_account_id != "") {
            if ($this->client_account_id <= 0) {
                $query->andWhere([$cardTableName . '.client_account_id' => null]);
            } else {
                $query->andWhere([$cardTableName . '.client_account_id' => $this->client_account_id]);
            }
        }
        $this->is_active && $query->andWhere([$cardTableName . '.is_active' => $this->is_active]);
        $this->status_id && $query->andWhere([$cardTableName . '.status_id' => $this->status_id]);

        $this->imsi && $query->andWhere([$imsiTableName . '.imsi' => $this->imsi]);
        $this->msisdn && $query->andWhere([$imsiTableName . '.msisdn' => $this->msisdn]);
        $this->did && $query->andWhere([$imsiTableName . '.did' => $this->did]);
        $this->imsi_partner && $query->andWhere([$imsiTableName . '.partner_id' => $this->imsi_partner]);
        $this->profile_id && $query->andWhere([$imsiTableName . '.profile_id' => $this->profile_id]);
        $this->region_id && $query->andWhere([$cardTableName . '.region_id' => $this->region_id]);

        if ($this->entry_point_id) {
            $queryAccount = clone $query;
            $accountIds = $queryAccount->select('client_account_id')->distinct()->column();

            $accountIds = ClientAccount::find()->alias('c')->joinWith('superClient')->where(['c.id' => $accountIds, 'entry_point_id' => $this->entry_point_id])->column();
            $query->andWhere([$cardTableName . '.client_account_id' => $accountIds]);
        }

        return $dataProvider;
    }
}
