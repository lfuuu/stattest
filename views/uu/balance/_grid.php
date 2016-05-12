<?php
/**
 * Бухгалтерский баланс. Грид
 *
 * @var \yii\web\View $this
 * @var ClientAccount $clientAccount
 * @var Currency $currency
 * @var AccountEntry[] $accountEntries
 * @var Payment[] $payments
 * @var array $accountEntrySummary
 * @var array $accountLogSetupSummary
 * @var array $accountLogPeriodSummary
 * @var array $accountLogResourceSummary
 * @var array $paymentSummary
 */

use app\classes\grid\GridView;
use app\classes\Html;
use app\classes\uu\model\AccountEntry;
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

    public $month = '';

    public $accountEntryUpdateTime = '';
    public $accountEntryPrice = '';
    public $accountEntryType = '';

    public $paymentDate = '';
    public $paymentBillNo = '';
    public $paymentSum = '';

    /**
     * Конвертировать из AccountEntry
     * @param AccountEntry $accountEntry
     * @return AccountEntryPaymentCo
     */
    public static function convertFromAccountEntry(AccountEntry $accountEntry)
    {
        $accountEntryPaymentCo = new self;
        $accountEntryPaymentCo->month = Yii::$app->formatter->asDate($accountEntry->date, 'php:M Y');
        $accountEntryPaymentCo->accountEntryUpdateTime = Yii::$app->formatter->asDatetime($accountEntry->update_time, 'medium');
        $accountEntryPaymentCo->accountEntryPrice = (($accountEntry->date == date('Y-m-01')) ? '0 / ' : '') .
            Html::a(
                sprintf('%+.2f', $accountEntry->price),
                $accountEntry->getUrl()
            );
        $accountEntryPaymentCo->accountEntryType = $accountEntry->getTypeName();

        $accountEntryPaymentCo->initialByMonth($accountEntry->price, 0);

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
        $accountEntryPaymentCo->month = $month = Yii::$app->formatter->asDate($payment->payment_date, 'php:M Y');
        $accountEntryPaymentCo->paymentDate = Yii::$app->formatter->asDate($payment->payment_date, 'medium');
        $accountEntryPaymentCo->paymentBillNo = Html::a(
            $payment->bill_no,
            sprintf('/?module=newaccounts&action=bill_view&bill=%s', $payment->bill_no)
        );
        $accountEntryPaymentCo->paymentSum = sprintf('%+.2f', -$payment->sum);

        $accountEntryPaymentCo->initialByMonth(0, -$payment->sum);

        return $accountEntryPaymentCo;
    }

    /**
     * помесячные суммы
     * @param float $accountEntryPrice
     * @param float $paymentSum
     */
    public function initialByMonth($accountEntryPrice, $paymentSum)
    {

        if (!isset(self::$accountEntryPriceByMonth[$this->month])) {
            self::$accountEntryPriceByMonth[$this->month] = 0;
        }
        self::$accountEntryPriceByMonth[$this->month] += $accountEntryPrice;

        if (!isset(self::$paymentSumByMonth[$this->month])) {
            self::$paymentSumByMonth[$this->month] = 0;
        }
        self::$paymentSumByMonth[$this->month] += $paymentSum;
    }
}

$accountEntryPaymentCos = [];
foreach ($accountEntries as $accountEntry) {
    $accountEntryPaymentCos[$accountEntry->date . ' accountEntry ' . $accountEntry->id] = AccountEntryPaymentCo::convertFromAccountEntry($accountEntry);
}
foreach ($payments as $payment) {
    $accountEntryPaymentCos[$payment->payment_date . ' payment ' . $payment->id] = AccountEntryPaymentCo::convertFromPayment($payment);
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
                        6 => Html::tag('h2', sprintf('%+.2f', AccountEntryPaymentCo::$paymentSumByMonth[$model->month])), // сумма платежей
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
            'label' => 'Сумма, ' . $currency->symbol,
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
            'attribute' => 'paymentBillNo',
            'label' => 'Счёт',
            'format' => 'html',
            'contentOptions' => [
                'class' => 'success',
            ],
        ],
        [
            'attribute' => 'paymentSum',
            'label' => 'Сумма, ' . $currency->symbol,
            'contentOptions' => [
                'class' => 'success bold',
            ],
        ],
    ],
]) ?>
