<?php

namespace app\models\filter;

use app\classes\DynamicModel;
use app\models\Bill;
use app\models\BillLine;
use app\models\Business;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContractReward;
use app\models\ClientContragent;
use app\models\PartnerRewards;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Expression;
use yii\db\Query;

class PartnerRewardsFilter extends DynamicModel
{

    public
        $partner_contract_id,
        $month,
        $isExtendsMode;

    public
        $contractsWithoutRewardSettings = [],
        $contractsWithIncorrectBusinessProcess = [],
        $partnersList = [],
        $summary = [],
        $possibleSummary = [];

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['month',], 'string'],
            [['partner_contract_id',], 'integer'],
        ];
    }

    /**
     * @param bool|false $isExtendsMode
     */
    public function __construct($isExtendsMode = false)
    {
        parent::__construct();

        $this->isExtendsMode = $isExtendsMode;
    }

    /**
     * @return $this
     */
    public function load()
    {
        parent::load(Yii::$app->request->get(), 'filter');

        $this->contractsWithoutRewardSettings = $this->_getContractsWithoutRewardSettings();
        $this->contractsWithIncorrectBusinessProcess = $this->_getContractsWithIncorrectBusinessProcess();
        $this->partnersList = $this->_getPartnersList();

        if (is_null($this->month)) {
            $this->month = (new \DateTime('now'))->format('Y-m');
        }

        return $this;
    }

    /**
     * @return bool|ArrayDataProvider
     */
    public function search()
    {
        if ($this->month && $this->partner_contract_id) {
            $query = new Query;

            $query->select([
                'rewards.*',
                'client_id' => 'client.id',
                'client_created' => 'client.created',
                'client.account_version',
                'contragent_name' => 'contragent.name',
                'bill_no' => 'bills.bill_no',
                'bill_paid' => 'bills.is_payed',
                'paid_summary' => 'bills.sum',
                'usage_type' => 'line.service',
                'usage_id' => 'line.id_service',
                'usage_paid' => 'line.sum',
            ]);

            $query
                ->from(['rewards' => PartnerRewards::tableName()])
                ->innerJoin(['bills' => Bill::tableName()], 'bills.id = rewards.bill_id')
                ->innerJoin(['client' => ClientAccount::tableName()], 'client.id = bills.client_id')
                ->innerJoin(['contract' => ClientContract::tableName()], 'contract.id = client.contract_id')
                ->innerJoin(['contragent' => ClientContragent::tableName()], 'contragent.id = contract.contragent_id')
                ->innerJoin(['line' => BillLine::tableName()], 'line.pk = rewards.line_pk');

            $query
                ->where(new Expression('DATE_FORMAT(rewards.created_at, "%Y-%m") = :month', ['month' => $this->month]))
                ->andWhere(['contragent.partner_contract_id' => $this->partner_contract_id])
                ->andWhere(['>=', 'line.sum', 0]);

            if (!$this->isExtendsMode) {
                $query->andWhere(['bills.is_payed' => Bill::STATUS_IS_PAID]);
            }

            $dataProvider = new ArrayDataProvider([
                'allModels' => $this->_prepareData($query),
                'sort' => false,
                'pagination' => false,
            ]);

            return $dataProvider;
        }

        return false;
    }

    /**
     * @return array
     */
    private function _getPartnersList()
    {
        $partners = ClientContract::find()
            ->andWhere(['business_id' => Business::PARTNER])
            ->innerJoin(ClientContragent::tableName(), ClientContragent::tableName() . '.id = contragent_id')
            ->orderBy(ClientContragent::tableName() . '.name')
            ->all();

        $partnerList = [];
        foreach ($partners as $partner) {
            $partnerList[$partner->id] = $partner->contragent->name . ' (#' . $partner->id . ')';
        }

        return $partnerList;
    }

    /**
     * @param Query $query
     * @return array
     */
    private function _prepareData(Query $query)
    {
        $data = [];

        foreach ($query->each(1000) as $record) {
            if (!array_key_exists($record['client_id'], $data)) {
                $data[$record['client_id']] = [
                    'client_id' => $record['client_id'],
                    'contragent_name' => $record['contragent_name'],
                    'client_created' => $record['client_created'],
                ];
            }

            $fieldPrefix = '';
            $summaryField = 'summary';
            if ($this->isExtendsMode) {
                if ((int)$record['bill_paid'] !== Bill::STATUS_IS_PAID) {
                    $fieldPrefix = 'possible_';
                    $summaryField = 'possibleSummary';
                }
            }

            $data[$record['client_id']]['paid_summary'] = $record['paid_summary'];
            $data[$record['client_id']]['details'][] = $record;

            $data[$record['client_id']][$fieldPrefix . 'once'] += $record['once'];
            $data[$record['client_id']][$fieldPrefix . 'percentage_once'] += $record['percentage_once'];
            $data[$record['client_id']][$fieldPrefix . 'percentage_of_fee'] += $record['percentage_of_fee'];
            $data[$record['client_id']][$fieldPrefix . 'percentage_of_over'] += $record['percentage_of_over'];
            $data[$record['client_id']][$fieldPrefix . 'percentage_of_margin'] += $record['percentage_of_margin'];

            $this->{$summaryField}['paid_summary'] += $record['usage_paid'];
            $this->{$summaryField}['once'] += $record['once'];
            $this->{$summaryField}['percentage_once'] += $record['percentage_once'];
            $this->{$summaryField}['percentage_of_fee'] += $record['percentage_of_fee'];
            $this->{$summaryField}['percentage_of_over'] += $record['percentage_of_over'];
            $this->{$summaryField}['percentage_of_margin'] += $record['percentage_of_margin'];
        }

        return $data;
    }

    /**
     * @return array
     */
    private function _getContractsWithoutRewardSettings()
    {
        $query = new Query;

        $query->select([
            'contract_id' => 'contract.id',
            'contragent_name' => 'contragent.name',
        ]);

        $query
            ->from([
                'partner' => new Expression('(
                    SELECT DISTINCT `partner_contract_id`
                    FROM `client_contragent`
                    WHERE `partner_contract_id` > 0
                )'),
                'contragent' => ClientContragent::tableName(),
                'contract' => ClientContract::tableName(),
            ])
            ->leftJoin(['rewards' => ClientContractReward::tableName(),], 'rewards.contract_id = contract.id');

        $query
            ->where('partner.partner_contract_id = contract.id')
            ->andWhere('contract.contragent_id = contragent.id')
            ->andWhere(['IS', 'rewards.id', new Expression('NULL')]);

        $query->orderBy([
            'contragent.name' => SORT_ASC
        ]);

        return $query->all();
    }

    /**
     * @return array
     */
    private function _getContractsWithIncorrectBusinessProcess()
    {
        $query = new Query;

        $query->select([
            'contract_id' => 'contract.id',
            'contragent_name' => 'contragent.name',
        ]);

        $query
            ->from([
                'partner' => new Expression('(
                    SELECT DISTINCT `partner_contract_id`
                    FROM `client_contragent`
                    WHERE `partner_contract_id` > 0
                )'),
                'contragent' => ClientContragent::tableName(),
                'contract' => ClientContract::tableName(),
            ])
            ->leftJoin(['rewards' => ClientContractReward::tableName(),], 'rewards.contract_id = contract.id');

        $query
            ->where('partner.partner_contract_id = contract.id')
            ->andWhere('contract.contragent_id = contragent.id')
            ->andWhere(['IS', 'rewards.id', new Expression('NULL')])
            ->andWhere(['!=', 'contract.business_id', Business::PARTNER]);

        $query->orderBy([
            'contragent.name' => SORT_ASC,
        ]);

        return $query->all();
    }

}