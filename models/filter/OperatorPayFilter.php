<?php

namespace app\models\filter;

use app\models\Bill;
use app\models\Business;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
use app\models\Organization;
use app\models\Payment;
use app\models\usages\UsageInterface;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Фильтрация для счетов
 */
class OperatorPayFilter extends Bill
{
    const BILL_TYPE_EXPENDITURE = 1;
    const BILL_TYPE_INCOME = 2;
    const BILL_TYPE_ALL = 3;

    const CHECKING_BILL_UNPAID_AND_UNVERIFIED = 1;
    const CHECKING_BILL_PAID_AND_UNVERIFIED = 2;
    const CHECKING_BILL_PAID_AND_VERIFIED = 3;
    const CHECKING_BILL_ALL = 4;

    public $pay_bill_until_from = '';
    public $pay_bill_until_to = '';

    public $bill_date_from = '';
    public $bill_date_to = '';

    public $payment_date_from = '';
    public $payment_date_to = '';

    public $sum_from = '';
    public $sum_to = '';

    public $client_id = '';
    public $bill_no = '';
    public $bill_date = '';
    public $sum = '';
    public $currency = '';
    public $pay_date = '';
    public $comment = '';

    public $organization_id = Organization::MCM_TELEKOM;
    public $bill_type = self::BILL_TYPE_ALL;
    public $checking_bill_state = self::CHECKING_BILL_PAID_AND_UNVERIFIED;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_id', 'sum_from', 'sum_to', 'organization_id', 'bill_type', 'checking_bill_state'], 'integer'],
            [
                [
                    'bill_no',
                    'currency',
                    'comment',
                    'bill_date_from',
                    'bill_date_to',
                    'pay_date_from',
                    'pay_date_to',
                    'pay_bill_until_from',
                    'pay_bill_until_to',
                    'payment_date_from',
                    'payment_date_to'
                ],
                'string'
            ],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return parent::attributeLabels() + [
                'payment_date' => 'Дата платежа',
                'organization_id' => 'Организация',
                'bill_type' => 'Тип счета',
                'checking_bill_state' => 'Состояние счета'
            ];
    }

    /**
     * Фильтровать
     *
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = (new Query())
            ->select(['b.*', 'name' => 'cg.name', 'p.payment_date'])
            ->from(['b' => Bill::tableName()])
            ->innerJoin(['c' => ClientAccount::tableName()], 'c.id = b.client_id')
            ->innerJoin(['ct' => ClientContract::tableName()], 'ct.id = c.contract_id')
            ->innerJoin(['cg' => ClientContragent::tableName()], 'cg.id = ct.contragent_id')
            ->leftJoin(['p' => Payment::tableName()], 'b.bill_no = p.bill_no')
            ->where(['ct.business_id' => Business::OPERATOR]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->client_id !== '' && $query->andWhere(['b.client_id' => $this->client_id]);
        $this->bill_no !== '' && $query->andWhere(['LIKE', 'b.bill_no', $this->bill_no . '%', false]);

        $this->bill_date_from !== '' && $query->andWhere(['>=', 'b.bill_date', $this->bill_date_from]);
        $this->bill_date_to !== '' && $query->andWhere(['<=', 'b.bill_date', $this->bill_date_to]);

        $this->pay_bill_until_from !== '' && $query->andWhere(['>=', 'b.pay_bill_until', $this->pay_bill_until_from]);
        $this->pay_bill_until_to !== '' && $query->andWhere(['<=', 'b.pay_bill_until', $this->pay_bill_until_to]);

        $this->payment_date_from !== '' && $query->andWhere(['>=', 'p.payment_date', $this->payment_date_from]);
        $this->payment_date_to !== '' && $query->andWhere(['<=', 'p.payment_date', $this->payment_date_to]);

        $this->sum_from !== '' && $query->andWhere(['<=', 'b.sum', $this->sum_from]);
        $this->sum_to !== '' && $query->andWhere(['>=', 'b.sum', $this->sum_to]);

        $this->currency !== '' && $query->andWhere(['b.currency' => $this->currency]);

        $this->comment !== '' && $query->andWhere(['LIKE', 'b.comment', $this->comment]);

        $this->organization_id !== '' && $query->andWhere(['ct.organization_id' => $this->organization_id]);

        if ($this->bill_type != self::BILL_TYPE_ALL) {
            $query->andWhere([($this->bill_type == self::BILL_TYPE_INCOME ? '>' : '<'), 'b.sum', 0]);
        }

        if ($this->checking_bill_state != self::CHECKING_BILL_ALL) {
            switch ($this->checking_bill_state) {
                // Неоплаченные и непроверенные
                case self::CHECKING_BILL_UNPAID_AND_UNVERIFIED:
                    $query
                        ->andWhere(['p.id' => null])
                        ->andWhere(['b.courier_id' => 0]);
                    break;

                // Оплаченные и непроверенные
                case self::CHECKING_BILL_PAID_AND_UNVERIFIED:
                    $query
                        ->andWhere(['IS NOT', 'p.id', null])
                        ->andWhere(['b.courier_id' => 0]);
                    break;

                // Оплаченные и проверенные
                case self::CHECKING_BILL_PAID_AND_VERIFIED:
                    $query
                        ->andWhere(['IS NOT', 'p.id', null])
                        ->andWhere(['NOT', ['b.courier_id' => 0]]);
                    break;
            }
        }

        $query->orderBy([
            // сначала полностью неоплаченные счета, потом частично
            new Expression('IF(payment_date IS NULL, :maxDate, payment_date) DESC', ['maxDate' => UsageInterface::MIDDLE_DATE]),
        ]);
        $query->addOrderBy([
            'pay_bill_until' => SORT_DESC
        ]);

        return $dataProvider;
    }

    /**
     * Список типов счетов для фильтрации
     *
     * @return array
     */
    public function getBillTypeList()
    {
        return [
            self::BILL_TYPE_EXPENDITURE => 'Расходные',
            self::BILL_TYPE_INCOME => 'Доходные',
            self::BILL_TYPE_ALL => 'Все',
        ];
    }

    /**
     * Список состояний счетов
     *
     * @return array
     */
    public function getCheckingBillStateList()
    {
        return [
            self::CHECKING_BILL_UNPAID_AND_UNVERIFIED => 'Неоплаченные и непроверенные',
            self::CHECKING_BILL_PAID_AND_UNVERIFIED => 'Оплаченные и непроверенные',
            self::CHECKING_BILL_PAID_AND_VERIFIED => 'Оплаченные и проверенные',
            self::CHECKING_BILL_ALL => 'Все',
        ];
    }
}
