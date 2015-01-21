<?php
namespace app\forms\buh;

use app\classes\Form;
use app\models\Bill;
use app\models\Currency;
use app\models\GoodsIncomeOrder;

class PaymentForm extends Form
{
    public $id;
    public $client_id;
    public $payment_date;
    public $oper_date;
    public $payment_no;
    public $bill_no;
    public $currency;
    public $sum;
    public $original_currency;
    public $original_sum;
    public $payment_rate;
    public $type;
    public $ecash_operator;
    public $comment;
    public $bank;

    public function rules()
    {
        return [
            [['id','client_id','payment_no',], 'integer'],
            [['bill_no','type','ecash_operator','comment','bank'], 'string'],
            [['payment_date','oper_date'], 'string'],
            [['currency','original_currency'], 'string'],
            [['sum','original_sum'], 'double'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'payment_no' => 'Номер платежа',
            'currency' => 'Валюта счета',
            'sum' => 'Сумма в валюте счета',
            'original_currency' => 'Валюта платежа',
            'original_sum' => 'Сумма в валюте платежа',
            'payment_date' => 'Дата платежа',
            'oper_date' => 'Дата проводки',
            'payment_rate' => 'Курс конвертации валюты',
            'type' => 'Тип платежа',
            'bank' => 'Банк',
            'ecash_operator' => 'Оператор эл. денег',
            'bill_no' => 'Привязать к счету',
            'comment' => 'Комментарий',
        ];
    }

    public function getAvailableBills()
    {
        $result = [];

        //добавляем не оплаченные счета
        $notPayedBills =
            Bill::find()
                ->select('bill_no,sum,currency')
                ->andWhere(['client_id' => $this->client_id, 'is_payed' => 0])
                ->orderBy('bill_no desc')
                ->asArray()
                ->all();
        if (!empty($notPayedBills)) {
            $result['-- не оплаченые счета --'] = [];
            foreach ($notPayedBills as $bill) {
                $result['-- не оплаченые счета --'][$bill['bill_no']] =
                    $bill['bill_no'] . ' = ' . $bill['sum'] . ' ' . Currency::symbol($bill['currency']);
            }
        }

        //добавляем частично оплаченные счета
        $partialPayedBills =
            Bill::find()
                ->select('bill_no,sum,currency')
                ->andWhere(['client_id' => $this->client_id, 'is_payed' => 2])
                ->orderBy('bill_no desc')
                ->asArray()
                ->all();
        if (!empty($partialPayedBills)) {
            $result['-- частично оплаченные счета --'] = [];
            foreach ($partialPayedBills as $bill) {
                $result['-- частично оплаченные счета --'][$bill['bill_no']] =
                    $bill['bill_no'] . ' = ' . $bill['sum'] . ' ' . Currency::symbol($bill['currency']);
            }
        }

        // добавляем оплаченные счета
        $payedBills =
            Bill::find()
                ->select('bill_no,sum,currency')
                ->andWhere(['client_id' => $this->client_id, 'is_payed' => 1])
                ->andWhere('is_payed > 0')
                ->orderBy('bill_no desc')
                ->asArray()
                ->all();
        if (!empty($payedBills)) {
            $result['-- оплаченые счета --'] = [];
            foreach ($payedBills as $bill) {
                $result['-- оплаченые счета --'][$bill['bill_no']] =
                    $bill['bill_no'] . ' = ' . $bill['sum'] . ' ' . Currency::symbol($bill['currency']);
            }
        }


        // добавляем не полаченые заказы поставщикам
        $incomeGoodsNotPayed =
            GoodsIncomeOrder::find()
                ->select('number,sum,currency')
                ->andWhere(['client_card_id' => $this->client_id, 'is_payed' => 0])
                ->orderBy('number desc')
                ->asArray()
                ->all();
        foreach($incomeGoodsNotPayed as $order) {
            $result['-- заказы не оплаченые --'] = [];
            $result['-- заказы не оплаченые --'][$order['number']] =
                $order['number'] . ' = ' . $order['sum'] . ' ' . Currency::symbol($order['currency']);
        }

        return $result;
    }
}