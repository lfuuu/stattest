<?php

namespace app\classes\accounting\accounting20;

use app\models\Bill;
use app\models\BillExternal;
use app\models\ClientAccount;
use app\models\Invoice;
use app\models\OperationType;
use app\models\Payment;
use app\modules\uu\models\AccountEntryCorrection;
use yii\base\BaseObject;

/**
 * Class lists
 */
class Lists extends BaseObject
{
    /** @var Payment[] $paysPlus */
    public $paysPlus = [];

    /** @var Payment[] $paysMinus */
    public $paysMinus = [];

    /** @var Invoice[] $invoices */
    public $invoices = [];

    /** @var Bill[] $billsPlus */
    public $billsPlus = [];

    /** @var Bill[] $billsMinus */
    public $billsMinus = [];

    /** @var BillExternal[] $invoiceExt */
    public $invoiceExt = [];

    /** @var AccountEntryCorrection[] $billCorrections */
    public $billCorrections = [];

    /** @var Bill[] $billInvoiceCorrections */
    public $billInvoiceCorrections = [];

    public ?ClientAccount $account = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->account) {
            throw new \InvalidArgumentException('account not set');
        }

        $account = $this->account;

//        $this->paysPlus = Payment::find()->where(['client_id' => $account->id])->andWhere(['payment_type' => Payment::PAYMENT_TYPE_INCOME] /* ['>', 'sum', 0] */)->all();
        //        $this->paysMinus = Payment::find()->where(['client_id' => $account->id])->andWhere(['payment_type' => Payment::PAYMENT_TYPE_OUTCOME] /* ['<', 'sum', 0] */)->all();
        $this->paysPlus = Payment::find()->where(['client_id' => $account->id])->andWhere(['>', 'sum', 0])->all();
        $this->paysMinus = Payment::find()->where(['client_id' => $account->id])->andWhere(['<', 'sum', 0])->all();

        $this->invoices = Invoice::find()
            ->alias('i')
            ->joinWith('bill b')
            ->where(['b.client_id' => $account->id])
//            ->orderBy(['date' => SORT_ASC])
            ->orderBy(['i.date' => SORT_ASC, 'i.number' => SORT_ASC, 'i.add_date' => SORT_ASC, 'i.id' => SORT_ASC])
            ->all();

        $this->billsPlus = Bill::find()
            ->where(['client_id' => $account->id])
            ->andWhere(['OR', ['>=', 'sum', 0], ['operation_type_id' => OperationType::ID_CORRECTION]])
            ->orderBy(['bill_date' => SORT_ASC])
            ->all();

        $this->billsMinus = Bill::find()
            ->where(['client_id' => $account->id])
            ->andWhere(['AND', ['<', 'sum', 0], ['NOT', ['operation_type_id' => OperationType::ID_CORRECTION]]])
            ->orderBy(['bill_date' => SORT_ASC])
            ->all();

        $this->invoiceExt = BillExternal::find()
            ->joinWith('bill b')
            ->with('bill')
            ->where(['b.client_id' => $account->id])
            ->orderBy([
                "STR_TO_DATE(ext_invoice_date, '%d-%m-%Y')" => SORT_ASC,
                'bill_date' => SORT_ASC,
                'b.id' => SORT_ASC,
            ])
            ->all();

        $this->billCorrections = array_reduce(AccountEntryCorrection::find()
            ->where(['client_account_id' => $account->id])
            ->asArray()
            ->all(),
            function (array $accum, array $value) {
                $accum[$value['bill_no']] = $value;
                return $accum;
            }, []);

        $this->billInvoiceCorrections = array_filter(
            array_map(
                function (Bill $b) {
                    $comment = $b->comment;
                    $m = [];
                    if ($comment && preg_match('/Автоматическая корректировка к счету (\d{6}-\d{6,7}) \((с\/ф №)?(\d)\)/', $comment, $m)) {
                        return ['bill_no' => $m[1], 'type_id' => $m[3], 'bill' => $b, 'is_found' => false];
                    }
                    return null;
                }, array_filter($this->billsPlus, function (Bill $b) {
                    return $b->operation_type_id == OperationType::ID_CORRECTION;
                })
            )
        );

        $this->billInvoiceCorrections = array_reduce($this->billInvoiceCorrections,
            function (array $accum, array $a) {
                $accum[$a['bill_no']][$a['type_id']] = $a;

                return $accum;
            },
            []
        );
    }
}