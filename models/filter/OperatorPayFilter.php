<?php

namespace app\models\filter;

use app\models\Bill;
use app\models\Business;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientContragent;
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

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['client_id', 'sum_from', 'sum_to'], 'integer'],
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
        return parent::attributeLabels() + ['payment_date' => 'Дата платежа'];
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
            ->where(['ct.business_id' => Business::OPERATOR])
            ->andWhere(['OR',
                ['p.bill_no' => null],
                ['!=', 'b.is_payed', Bill::STATUS_IS_PAID]
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->client_id !== '' && $query->andWhere(['b.client_id' => $this->client_id]);
        $this->bill_no !== '' && $query->andWhere(['LIKE', 'b.bill_no', $this->bill_no . '%', true]);

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

        $query->orderBy([
            // сначала полностью неоплаченные счета, потом частично
            new Expression('IF(payment_date IS NULL, :maxDate, payment_date) DESC', ['maxDate' => UsageInterface::MIDDLE_DATE]),
        ]);
        $query->addOrderBy([
            'pay_bill_until' => SORT_DESC
        ]);

        return $dataProvider;
    }
}
