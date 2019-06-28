<?php

namespace app\forms\voipreport;

use app\classes\traits\AddClientAccountFilterTraits;
use app\models\Bill;
use app\models\Business;
use app\models\BusinessProcess;
use app\models\BusinessProcessStatus;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\ClientCounter;
use app\models\ContractType;
use app\models\CounterInteropTrunk;
use app\models\Payment;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class BalanceReport extends Model
{
    use AddClientAccountFilterTraits;

    public $id;
    public $account_manager;
    public $contract_type_id;
    public $balance_from;
    public $balance_to;
    public $credit_from;
    public $credit_to;
    public $realtime_balance_from;
    public $realtime_balance_to;
    public $currency;
    public $b_id;
    public $bp_id;
    public $bps_id;
    public $name_full;
    public $income_sum_from;
    public $income_sum_to;
    public $outcome_sum_from;
    public $outcome_sum_to;
    public $inc_out_sum_from;
    public $inc_out_sum_to;
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'b_id',
                    'bp_id',
                    'bps_id',
                    'contract_type_id',
                    'balance_from',
                    'balance_to',
                    'credit_from',
                    'credit_to',
                    'realtime_balance_from',
                    'realtime_balance_to',
                    'income_sum_from',
                    'income_sum_to',
                    'outcome_sum_from',
                    'outcome_sum_to',
                    'inc_out_sum_from',
                    'inc_out_sum_to'
                ], 'integer'
            ],
            [['name_full', 'currency', 'account_manager'], 'string'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ЛС',
            'account_manager' => 'Аккаунт менеджер',
            'contract_type_name' => 'Тип договора',
            'balance' => 'Бухгалтерский баланс',
            'realtime_balance' => 'Реалтаймовый баланс',
            'credit' => 'Кредитный лимит',
            'currency' => 'Валюта',
            'name_full' => 'Название компании',
            'b_id' => 'Подразделение',
            'bp_id' => 'Бизнес процесс',
            'bps_id' => 'Статус бизнес процесса',
            'income_sum' => 'Оригинация',
            'outcome_sum' => 'Терминация',
            'inc_out_sum' => 'Оригинация+Терминация',
        ];
    }

    /**
     * @return ArrayDataProvider
     */
    public function search($isValidated = true)
    {
        $clientAccountTableName = ClientAccount::tableName();
        $clientContractTableName = ClientContract::tableName();
        $contragentTableName = ClientContragent::tableName();
        $businessTableName = Business::tableName();
        $businessProcessTableName = BusinessProcess::tableName();
        $businessProcessStatusTableName = BusinessProcessStatus::tableName();
        $counterInteropTrunkTableName = CounterInteropTrunk::tableName();
        $billsTableName = Bill::tableName();
        $paymentsTableName = Payment::tableName();
        $contractTypeTableName = ContractType::tableName();
        $clientCounterTableName = ClientCounter::tableName();
        $query = ClientAccount::find()
            ->joinWith([
                'clientContractModel.business',
                'clientContractModel.businessProcess',
                'clientContractModel.businessProcessStatus',
                'counterInteropTrunks',
                'clientCounter',
                'clientContractModel.clientContragent'
            ])
            ->leftJoin($contractTypeTableName, $clientContractTableName . '.contract_type_id = ' . $contractTypeTableName . '.id')
            ->select([
                $clientAccountTableName . '.id',
                '((select sum(sum) from ' . $billsTableName . ' where client_id = ' . $clientAccountTableName . '.id)'
                . '-' .
                '(select sum(sum) from ' . $paymentsTableName . ' where client_id = ' . $clientAccountTableName . '.id)) as balance',
                $clientAccountTableName . '.currency',
                $clientAccountTableName . '.credit',
                $contragentTableName . '.name_full',
                $businessTableName . '.name as b_name',
                $businessProcessTableName . '.name as bp_name',
                $businessProcessStatusTableName . '.name as bps_name',
                $clientContractTableName . '.account_manager',
                $clientContractTableName . '.business_id',
                $clientContractTableName . '.business_process_id',
                '(' . $clientAccountTableName . '.balance + ' . $clientCounterTableName . '.amount_sum) as realtime_balance',
                $contractTypeTableName . '.name as contract_type_name',
                $counterInteropTrunkTableName . '.income_sum',
                $counterInteropTrunkTableName . '.outcome_sum',
                '(income_sum + outcome_sum) as inc_out_sum',
            ])
            ->asArray();

        $dataProvider = new ArrayDataProvider([
            'sort' => [
                'attributes' => [
                    'id',
                    'account_manager',
                    'contract_type_name',
                    'credit',
                    'balance',
                    'realtime_balance',
                    'currency',
                    'name_full',
                    'b_id',
                    'bp_id',
                    'bps_id',
                    'income_sum',
                    'outcome_sum',
                    'inc_out_sum'
                ]
            ]
        ]);


        if (!$isValidated) {
            $dataProvider->allModels = $query->where('0=1')->all();
            return $dataProvider;
        }

        $query->andFilterWhere(['like', $clientAccountTableName . '.id', $this->id]);
        $query->andFilterWhere(['like', $clientContractTableName . '.account_manager', $this->account_manager]);
        $query->andFilterWhere([$clientAccountTableName . '.currency' => $this->currency]);
        $query->andFilterWhere(['like', $contragentTableName . '.name_full', $this->name_full]);
        $query->andFilterWhere([$businessTableName . '.id' => ($this->b_id) ? $this->b_id : Business::OPERATOR]);
        $query->andFilterWhere([$businessProcessTableName . '.id' => $this->bp_id]);
        $query->andFilterWhere([$businessProcessStatusTableName . '.id' => $this->bps_id]);
        $query->andFilterWhere([$clientContractTableName . '.contract_type_id' => $this->contract_type_id]);
        $query->andFilterWhere(['>=', 'credit', $this->credit_from]);
        $query->andFilterWhere(['<=', 'credit', $this->credit_to]);

        $query->andFilterHaving(['>=', 'balance', $this->balance_from]);
        $query->andFilterHaving(['<=', 'balance', $this->balance_to]);

        $query->andFilterHaving(['>=', 'realtime_balance', $this->realtime_balance_from]);
        $query->andFilterHaving(['<=', 'realtime_balance', $this->realtime_balance_to]);

        $query->andFilterWhere(['>=', 'income_sum', $this->income_sum_from]);
        $query->andFilterWhere(['<=', 'income_sum', $this->income_sum_to]);
        $query->andFilterWhere(['>=', 'outcome_sum', $this->outcome_sum_from]);
        $query->andFilterWhere(['<=', 'outcome_sum', $this->outcome_sum_to]);

        $query->andFilterHaving(['>=', 'inc_out_sum', $this->inc_out_sum_from]);
        $query->andFilterHaving(['<=', 'inc_out_sum', $this->inc_out_sum_to]);

        $dataProvider->allModels = $query->all();

        return $dataProvider;
    }

}
