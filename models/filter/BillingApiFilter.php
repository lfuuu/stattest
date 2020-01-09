<?php

namespace app\models\filter;

use app\classes\grid\ActiveDataProvider;
use app\models\billing\api\ApiRaw;
use Yii;
use yii\db\Expression;

class BillingApiFilter extends ApiRaw
{
    public $accountId = null;
    public $isLoad = false;

    public
        $connect_time_from = '',
        $connect_time_to = '',

        $api_weight_from = '',
        $api_weight_to = '',

        $rate_from = '',
        $rate_to = '',
        
        $cost_from = '',
        $cost_to = ''
    ;

    public function rules()
    {
        $fieldList = ['connect_time_from', 'connect_time_to', 'api_weight_from', 'api_weight_to', 'rate_from', 'rate_to', 'cost_from', 'cost_to'];
        return array_merge(parent::rules(), [
            [$fieldList, 'required'],
            [array_merge($fieldList, ['api_method_id']), 'string'],
        ]);
    }

    /**
     * @param int $clientId
     * @return $this
     */
    public function load($clientId)
    {
        $this->accountId = $clientId;

        parent::load(Yii::$app->request->get());

        return $this;
    }

    private function makeQuery()
    {
        $query = ApiRaw::find()->with('method');

        if (!($this->connect_time_from && $this->connect_time_to && $this->accountId)) {
            $query->andWhere('false');
        } else {
            $this->isLoad = true;
            $query->andWhere(['between', 'connect_time', $this->connect_time_from . ' 00:00:00', $this->connect_time_to . ' 23:59:59.999999']);
        }

        $query->andWhere(['account_id' => $this->accountId]);

        $this->api_method_id && $query->andWhere(['api_method_id' => $this->api_method_id]);
        $this->api_weight_from && $query->andWhere(['>=', 'api_weight', $this->api_weight_from]);
        $this->api_weight_to && $query->andWhere(['<=', 'api_weight', $this->api_weight_to]);

        return $query;
    }

    /**
     * @return bool|ActiveDataProvider
     */
    public function search()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $this->makeQuery(),
            'db' => ApiRaw::getDb(),
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_ASC,
                ]
            ],
        ]);

        return $dataProvider;
    }

    public function getTotal()
    {
        $query = $this->makeQuery();

        $query->select(['sum' => new Expression('-sum(cost)')]);

        return $query->scalar();
    }
}