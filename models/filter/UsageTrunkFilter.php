<?php

namespace app\models\filter;

use yii\db\Query;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\UsageTrunk;
use app\models\billing\Trunk;

class UsageTrunkFilter extends UsageTrunk
{

    public
        $connection_point_id,
        $trunk_ids,
        $contragent_id,
        $contract_number,
        $contract_type_id,
        $business_process_id,
        $trunk_id,
        $what_is_enabled;

    private $trunkIDs = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'connection_point_id',
                    'contragent_id',
                    'contract_type_id',
                    'business_process_id',
                    'trunk_id',
                ],
                'integer'
            ],
            [['what_is_enabled', 'trunk_ids', 'contract_number',], 'string'],
        ];
    }

    /**
     * @param array $data
     * @param null|string $formName
     * @return bool
     */
    public function load($data, $formName = null)
    {
        $params = parent::load($data, $formName);

        if (isset($this->trunk_ids) && $this->trunk_ids !== '') {
            $this->trunkIDs = explode(',', $this->trunk_ids);
        }

        return $params;
    }

    /**
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = new Query;

        $query->select([
            'usage_trunk_id' => 'trunk.id',
            'trunk.*',
            'contract_number' => 'contract.number',
            'business_process_id' => 'contract.business_process_id',
            'contract_type_id' => 'contract.contract_type_id',
        ]);

        $query
            ->from(['trunk' => UsageTrunk::tableName()])
            ->leftJoin(
                ['client' => ClientAccount::tableName()],
                'client.id = trunk.client_account_id'
            )
            ->leftJoin(
                ['contract' => ClientContract::tableName()],
                'contract.id = client.contract_id'
            );

        $query->andWhere([
            '<=',
            'trunk.activation_dt',
            new Expression('CAST(NOW() AS DATETIME)')
        ]);
        $query->andWhere([
            '>=',
            'trunk.expire_dt',
            new Expression('CAST(NOW() AS DATETIME)')
        ]);

        $query->orderBy([
            'trunk.connection_point_id' => SORT_DESC,
            'client.super_id' => SORT_ASC,
            'trunk.trunk_id' => SORT_ASC,
        ]);

        /**
         * Установка условий фильтрации
         */
        !empty($this->connection_point_id) && $query->andWhere(['trunk.connection_point_id' => $this->connection_point_id]);
        is_array($this->trunkIDs) && count($this->trunkIDs) > 0 && $query->andWhere([
            'IN',
            'trunk.trunk_id',
            $this->trunkIDs
        ]);
        !empty($this->contragent_id) && $query->andWhere(['contract.contragent_id' => $this->contragent_id]);
        !empty($this->contract_number) && $query->andWhere(['LIKE', 'contract.number', $this->contract_number]);
        !empty($this->contract_type_id) && $query->andWhere(['contract.contract_type_id' => $this->contract_type_id]);
        !empty($this->business_process_id) && $query->andWhere(['contract.business_process_id' => $this->business_process_id]);
        !empty($this->trunk_id) && $query->andWhere(['trunk.trunk_id' => $this->trunk_id]);
        switch ($this->what_is_enabled) {
            case Trunk::TRUNK_DIRECTION_ORIG: {
                $query->andWhere(['trunk.orig_enabled' => 1]);
                break;
            }
            case Trunk::TRUNK_DIRECTION_TERM: {
                $query->andWhere(['trunk.term_enabled' => 1]);
                break;
            }
            case Trunk::TRUNK_DIRECTION_BOTH: {
                $query->andWhere([
                    'trunk.orig_enabled' => 1,
                    'trunk.term_enabled' => 1,
                ]);
                break;
            }
        }
        !$this->isFilteringPossible() && $query->where('false');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => false,
        ]);

        return $dataProvider;
    }

    /**
     * Указаны ли необходимые фильтры. Если нет, то фильтрация не происходит
     * @return bool
     */
    public function isFilteringPossible()
    {
        return (int)$this->connection_point_id;
    }

}