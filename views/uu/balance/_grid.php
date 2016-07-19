<?php
/**
 * Бухгалтерский баланс. Грид
 *
 * @var \yii\web\View $this
 * @var ClientAccount $clientAccount
 * @var Currency $currency
 * @var AccountEntry[] $accountEntries
 * @var Payment[] $payments
 * @var \app\classes\uu\model\Bill[] $accountBills
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

/*
$accountEntryPaymentCos = [];

foreach ($accountEntries as $accountEntry) {
    // проводки
    $accountEntryPaymentCos[$accountEntry->date . ' accountEntry ' . $accountEntry->id] = AccountEntryPaymentCo::convertFromAccountEntry($accountEntry);
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
*/

$result = [];

foreach ($accountBills as $bill) {
    $billShortDate = (new DateTime($bill->date))->format('Y-m');

    foreach ($bill->accountEntries as $item) {
        $itemShortDate = (new DateTime($item->date))->format('Y-m');
        $result[$itemShortDate]['uuItems'][] = $item;
    }

    $result[$billShortDate]['uuBills'][] = $bill;
    $result[$billShortDate]['totalUuItems'] += count($bill->accountEntries);
}

foreach ($bills as $bill) {
    $billShortDate = (new DateTime($bill->bill_date))->format('Y-m');

    foreach ($bill->lines as $item) {
        $itemShortDate = (new DateTime($item->date_from))->format('Y-m');
        $result[$itemShortDate]['oldItems'][] = $item;
    }

    $result[$billShortDate]['oldBills'][] = $bill;
    $result[$billShortDate]['totalOldItems'] += count($bill->lines);
}

foreach ($payments as $payment) {
    $billShortDate = (new DateTime($payment->payment_date))->format('Y-m');
    $result[$billShortDate]['payments'][] = AccountEntryPaymentCo::convertFromPayment($payment);
}

foreach ($result as $monthKey => $month):
    $totalItems = $month['totalUuItems'] ?: 0;
    if ($totalItems < $month['totalOldItems']) {
        $totalItems = $month['totalOldItems'];
    }
    ?>
    <table border="0" class="table table-bordered table-striped">
        <colgroup>
            <col width="10%" />
            <col width="20%" />
            <col width="10%" />
            <col width="20%" />
            <col width="10%" />
            <col width="20%" />
            <col width="10%" />
        </colgroup>
        <thead>
            <tr>
                <th colspan="2">
                    <div style="overflow: hidden; float: left; font-size: 14px;">
                        <?= datefmt_format_object(new DateTime($monthKey), 'LLL Y', Yii::$app->formatter->locale) ?>
                    </div>
                    <div class="text-center" style="overflow: hidden;">Счет-фактура</div>
                </th>
                <th colspan="2">Новые счета</th>
                <th colspan="2">Старые счета</th>
                <th>Платежи</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i=0; $i < $totalItems; $i++): ?>
                <tr>
                    <?php if (!isset($month['uuBills'][$i]) && count($month['uuBills']) == $i): ?>
                        <td rowspan="<?= ($totalItems - $i) ?>"></td>
                    <?php elseif (isset($month['uuBills'][$i])): ?>
                        <td>
                            <a href="<?= \yii\helpers\Url::toRoute(['/uu/invoice/view', 'month' => (new DateTime($month['uuBills'][$i]->date))->format('Y-m')]) ?>" target="_blank">Счет-фактура № <?= $month['uuBills'][$i]->id ?></a>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?php if (isset($month['uuItems'][$i])): ?>
                            <abbr title="ID не универсальной услуги"><?= $month['uuItems'][$i]->accountTariff->getNonUniversalId() ?></abbr>:
                            <?php
                            switch ($month['uuItems'][$i]->type_id) {
                                case -2:
                                    echo '<i>' . $month['uuItems'][$i]->getTypeName() . '</i>';
                                    break;
                                default:
                                    echo $month['uuItems'][$i]->getTypeName();
                                    break;
                            }
                            ?>
                            (<?= $month['uuItems'][$i]->price_with_vat ?>)
                        <?php endif; ?>
                    </td>
                    <td></td>
                    <td></td>
                    <?php if (!isset($month['oldBills'][$i]) && count($month['oldBills']) == $i): ?>
                        <td rowspan="<?= ($totalItems - $i) ?>"></td>
                    <?php elseif (isset($month['oldBills'][$i])) :?>
                        <td>
                            <a href="<?= \yii\helpers\Url::toRoute(['/', 'module' => 'newaccounts', 'action' => 'bill_view', 'bill' => $month['oldBills'][$i]->bill_no]) ?>" class="bill-info" data-bill="<?= $month['oldBills'][$i]->bill_no ?>" target="_blank">Счет № <?= $month['oldBills'][$i]->bill_no ?></a>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?php if (isset($month['oldItems'][$i])): ?>
                            <div data-bill="<?= $month['oldItems'][$i]->bill_no ?>">
                                <abbr title="ID не универсальной услуги"><?= $month['oldItems'][$i]->id_service ?></abbr>:
                                <?php
                                if ($month['oldItems'][$i]->date_from !== $month['oldItems'][$i]->bill->bill_date) {
                                    echo 'Плата за услугу';
                                }
                                elseif ((new DateTime($month['oldItems'][$i]->date_from))->format('Y-m') == $monthKey) {
                                    echo '<i>Абонентка</i>';
                                }
                                ?>
                                (<?= $month['oldItems'][$i]->price ?>)
                            </div>
                        <?php endif; ?>
                    </td>
                    <?php if (!isset($month['uuBills'][$i]) && count($month['uuBills']) == $i): ?>
                        <td rowspan="<?= ($totalItems - $i) ?>"></td>
                    <?php elseif (isset($month['payments'][$i])): ?>
                        <td>
                            <?= $month['payments'][$i]->paymentDate ?>
                            (<?= $month['payments'][$i]->paymentSum ?>)
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
<?php endforeach; ?>

<script type="text/javascript">
jQuery(document).ready(function() {
    $('a.bill-info')
        .hover(
            function() {
                $('div[data-bill="' + $(this).data('bill') + '"]').css('background-color', '#C0C0D0');
            },
            function() {
                $('div[data-bill="' + $(this).data('bill') + '"]').css('background-color', 'inherit');
            }
        )
});
</script>

<?
/*GridView::widget([
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
])
*/
?>