<?php
/**
 * Бухгалтерский баланс. Грид
 *
 * @var \yii\web\View $this
 * @var ClientAccount $clientAccount
 * @var Currency $currency
 * @var AccountEntry[] $accountEntries
 * @var Payment[] $payments
 * @var Bill[] $bills
 * @var array $accountEntrySummary
 * @var array $accountLogSetupSummary
 * @var array $accountLogPeriodSummary
 * @var array $accountLogResourceSummary
 * @var array $paymentSummary
 */

use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\model\AccountEntry;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\Payment;
use yii\data\ArrayDataProvider;

/**
 * Гибрид AccountEntry и Payment
 */
class AccountEntryPaymentCo
{
    public static $accountEntryPriceByMonth = [];
    public static $paymentSumByMonth = [];
    public static $billSumByMonth = [];

    public $month = '';

    public $accountEntryUpdateTime = '';
    public $accountEntryPrice = '';
    public $accountEntryType = '';

    public $paymentDate = '';
    public $paymentSum = '';

    public $billDate = '';
    public $billItem = '';
    public $billBillNo = '';
    public $billSum = '';

    /**
     * Конвертировать из AccountEntry
     * @param AccountEntry $accountEntry
     * @return AccountEntryPaymentCo
     */
    public static function convertFromAccountEntry(AccountEntry $accountEntry)
    {
        $accountEntryPaymentCo = new self;
        $accountEntryPaymentCo->month = datefmt_format_object(new DateTime($accountEntry->date), 'LLL Y', Yii::$app->formatter->locale); // нативный php date не поддерживает LLL/LLLL
        $accountEntryPaymentCo->accountEntryUpdateTime = Yii::$app->formatter->asDate($accountEntry->update_time, 'php: d M');
        $accountEntryPaymentCo->accountEntryPrice = (($accountEntry->date == date('Y-m-01')) ? '0 / ' : '') .
            Html::a(
                sprintf('%+.2f', $accountEntry->price),
                $accountEntry->getUrl()
            );
        $accountEntryPaymentCo->accountEntryType = $accountEntry->getTypeName();

        $accountEntryPaymentCo->initialByMonth($accountEntry->price, 0, 0);

        return $accountEntryPaymentCo;
    }

    /**
     * Конвертировать из Payment
     * @param Payment $payment
     * @return AccountEntryPaymentCo
     */
    public static function convertFromPayment(Payment $payment)
    {
        $accountEntryPaymentCo = new self;
        $accountEntryPaymentCo->month = datefmt_format_object(new DateTime($payment->payment_date), 'LLL Y', Yii::$app->formatter->locale); // нативный php date не поддерживает LLL/LLLL
        $accountEntryPaymentCo->paymentDate = Yii::$app->formatter->asDate($payment->payment_date, 'php: d M');
//        $accountEntryPaymentCo->billBillNo = Html::a(
//            $payment->bill_no,
//            sprintf('/?module=newaccounts&action=bill_view&bill=%s', $payment->bill_no)
//        );
        $accountEntryPaymentCo->paymentSum = sprintf('%+.2f', -$payment->sum);

        $accountEntryPaymentCo->initialByMonth(0, -$payment->sum, 0);

        return $accountEntryPaymentCo;
    }

    /**
     * Конвертировать из BillLine
     * @param BillLine $billLine
     * @return AccountEntryPaymentCo
     */
    public static function convertFromBillLine(BillLine $billLine)
    {
        $accountEntryPaymentCo = new self;
        $accountEntryPaymentCo->month = datefmt_format_object(new DateTime($billLine->date_from), 'LLL Y', Yii::$app->formatter->locale); // нативный php date не поддерживает LLL/LLLL
        $accountEntryPaymentCo->billDate =
            Yii::$app->formatter->asDate($billLine->date_from, 'php:d') . '-' .
            Yii::$app->formatter->asDate($billLine->date_to, 'php:d M');
        $accountEntryPaymentCo->billBillNo = Html::a(
            $billLine->bill_no,
            sprintf('/?module=newaccounts&action=bill_view&bill=%s', $billLine->bill_no)
        );
        $accountEntryPaymentCo->billItem = $billLine->item;
        $accountEntryPaymentCo->billSum = sprintf('%+.2f', -$billLine->sum);

        $accountEntryPaymentCo->initialByMonth(0, 0, -$billLine->sum);

        return $accountEntryPaymentCo;
    }

    /**
     * помесячные суммы
     * @param float $accountEntryPrice
     * @param float $paymentSum
     * @param float $billSum
     */
    public function initialByMonth($accountEntryPrice, $paymentSum, $billSum)
    {

        if (!isset(self::$accountEntryPriceByMonth[$this->month])) {
            self::$accountEntryPriceByMonth[$this->month] = 0;
        }
        self::$accountEntryPriceByMonth[$this->month] += $accountEntryPrice;


        if (!isset(self::$paymentSumByMonth[$this->month])) {
            self::$paymentSumByMonth[$this->month] = 0;
        }
        self::$paymentSumByMonth[$this->month] += $paymentSum;


        if (!isset(self::$billSumByMonth[$this->month])) {
            self::$billSumByMonth[$this->month] = 0;
        }
        self::$billSumByMonth[$this->month] += $billSum;
    }
}

$accountEntryPaymentCos = [];

foreach ($accountEntries as $accountEntry) {
    // проводки
    $accountEntryPaymentCos[$accountEntry->date . ' accountEntry ' . $accountEntry->id] = AccountEntryPaymentCo::convertFromAccountEntry($accountEntry);
}

foreach ($payments as $payment) {
    // платежи
    $accountEntryPaymentCos[$payment->payment_date . ' payment ' . $payment->id] = AccountEntryPaymentCo::convertFromPayment($payment);
}

foreach ($bills as $bill) {
    // старые счета
    $bilLines = $bill->lines;
    foreach ($bilLines as $bilLine) {
        // строчки старого счёта
        $accountEntryPaymentCos[$bilLine->date_from . ' bill ' . $bilLine->pk] = AccountEntryPaymentCo::convertFromBillLine($bilLine);
    }
}

krsort($accountEntryPaymentCos);

$dataProvider = new ArrayDataProvider([
    'allModels' => $accountEntryPaymentCos,
]);
?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'extraButtons' =>
        $this->render('//uu/bill/_ico', ['clientAccountId' => $clientAccount->id]) . ' ' .
        $this->render('//uu/invoice/_ico', ['clientAccountId' => $clientAccount->id]),
//        $this->render('//uu/balance/_ico', ['clientAccountId' => $clientAccount->id]),
    'columns' => [
        // месяц
        [
            'attribute' => 'month',
            'label' => 'Месяц',
            'group' => true,  // группировать по этому полю
            'groupedRow' => true, // вынести это не ячейкой, а на отдельную строку ДО
            'groupOddCssClass' => 'hidden',  // и скрыть эту отдельную строку, вместо нее - групповая сумма
            'groupEvenCssClass' => 'hidden', // и скрыть эту отдельную строку, вместо нее - групповая сумма
            'groupHeader' => function (AccountEntryPaymentCo $model, $key, $index, $widget) { // Closure method
                return [
                    'content' => [
                        1 => Html::tag('h2', $model->month), // месяц
                        2 => Html::tag('h2', sprintf('%+.2f', AccountEntryPaymentCo::$accountEntryPriceByMonth[$model->month])), // сумма проводок
                        5 => Html::tag('h2', sprintf('%+.2f', AccountEntryPaymentCo::$paymentSumByMonth[$model->month])), // сумма платежей
                        9 => Html::tag('h2', sprintf('%+.2f', AccountEntryPaymentCo::$billSumByMonth[$model->month])), // сумма счетов
                    ],
                    'options' => [
                        'class' => 'info',
                    ],
                ];
            },
        ],

        // проводка
        [
            'attribute' => 'accountEntryUpdateTime',
            'label' => 'Время расчёта',
            'contentOptions' => [
                'class' => 'warning',
            ],
        ],
        [
            'attribute' => 'accountEntryPrice',
            'label' => 'Сумма проводки, ' . $currency->symbol,
            'format' => 'html',
            'contentOptions' => [
                'class' => 'warning  bold',
            ],
        ],
        [
            'attribute' => 'accountEntryType',
            'label' => 'Тип проводки',
            'contentOptions' => [
                'class' => 'warning',
            ],
        ],

        // платеж
        [
            'attribute' => 'paymentDate',
            'label' => 'Дата платежа',
            'contentOptions' => [
                'class' => 'success',
            ],
        ],
        [
            'attribute' => 'paymentSum',
            'label' => 'Сумма платежа, ' . $currency->symbol,
            'contentOptions' => [
                'class' => 'success bold',
            ],
        ],

        // старый счёт
        [
            'attribute' => 'billDate',
            'label' => 'Счёт',
            'contentOptions' => [
                'class' => 'warning',
            ],
        ],
        [
            'attribute' => 'billBillNo',
            'label' => 'Счёт',
            'format' => 'html',
            'contentOptions' => [
                'class' => 'warning',
            ],
        ],
        [
            'attribute' => 'billItem',
            'label' => 'Позиция счёта',
            'contentOptions' => [
                'class' => 'warning',
            ],
        ],
        [
            'attribute' => 'billSum',
            'label' => 'Сумма счёта, ' . $currency->symbol,
            'contentOptions' => [
                'class' => 'warning bold',
            ],
        ],
    ],
]) ?>
