<?php

use yii\helpers\Url;
use app\classes\Html;
use app\classes\uu\model\AccountEntry;
use app\classes\uu\model\Bill as uuBill;
use app\models\Bill;
use app\models\BillLine;
use app\models\ClientAccount;
use app\models\Payment;


/**
 * Бухгалтерский баланс. Грид
 *
 * @var \yii\web\View $this
 * @var ClientAccount $clientAccount
 * @var Currency $currency
 * @var AccountEntry[] $accountEntries
 * @var Payment[] $payments
 * @var uuBill[] $uuBills
 * @var Bill[] $billsUsage
 * @var Bill[] $billsUniversal
 * @var array $accountEntrySummary
 * @var array $accountLogSetupSummary
 * @var array $accountLogPeriodSummary
 * @var array $accountLogResourceSummary
 * @var array $paymentSummary
 */

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
     * Конвертировать из Payment
     * @param Payment $payment
     * @return AccountEntryPaymentCo
     */
    public static function convertFromPayment(Payment $payment)
    {
        $accountEntryPaymentCo = new self;
        $accountEntryPaymentCo->month = datefmt_format_object(new DateTime($payment->payment_date), 'LLL Y', Yii::$app->formatter->locale); // нативный php date не поддерживает LLL/LLLL
        $accountEntryPaymentCo->paymentDate = Yii::$app->formatter->asDate($payment->payment_date, 'php: d M');
        $accountEntryPaymentCo->paymentSum = sprintf('%+.2f', -$payment->sum);

        $accountEntryPaymentCo->initialByMonth(0, -$payment->sum, 0);

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

$result = [];

// Формирование массива универсальных счетов и проводок
foreach ($uuBills as $bill) {
    // Определение месяца/года
    $billShortDate = (new DateTime($bill->date))->format('Y-m');

    // Заполнение массива проводок на месяц
    foreach ($bill->accountEntries as $item) {
        $itemShortDate = (new DateTime($item->date))->format('Y-m');
        $result[$itemShortDate]['uuItems'][] = $item;
    }

    // Заполнение массива счетов на месяц
    $result[$billShortDate]['uuBills'][] = $bill;
    $result[$billShortDate]['totalUuItems'] += count($bill->accountEntries);
}

// Формирование массив старых счетов и позиций
foreach ($billsUsage as $bill) {
    // Определение месяца/года
    $billShortDate = (new DateTime($bill->bill_date))->format('Y-m');

    // Заполнение массива позиций на месяц
    foreach ($bill->getLines()->orderBy(['id_service' => SORT_ASC])->all() as $item) {
        $itemShortDate = (new DateTime($item->date_from))->format('Y-m');
        $result[$itemShortDate]['oldItems'][] = $item;
    }

    // Заполнение массива счетов на месяц
    $result[$billShortDate]['oldBills'][] = $bill;
    $result[$billShortDate]['totalOldItems'] += count($bill->lines);
}

foreach ($billsUniversal as $bill) {
    // Определение месяца/года
    $billShortDate = (new DateTime($bill->bill_date))->format('Y-m');

    // Заполнение массива позиций на месяц
    foreach ($bill->getLines()->orderBy(new \yii\db\Expression('CAST(item_id as UNSIGNED)'))->all() as $item) {
        $itemShortDate = (new DateTime($item->date_from))->format('Y-m');
        $result[$itemShortDate]['oldUUItems'][] = $item;
    }

    // Заполнение массива счетов на месяц
    $result[$billShortDate]['oldUUBills'][] = $bill;
    $result[$billShortDate]['totalOldUUItems'] += count($bill->lines);
}

// Формирование массив платежей
foreach ($payments as $payment) {
    $billShortDate = (new DateTime($payment->payment_date))->format('Y-m');
    $result[$billShortDate]['payments'][] = AccountEntryPaymentCo::convertFromPayment($payment);
}

foreach ($result as $monthKey => $month):
    $totalItems = max($month['totalUuItems'], $month['totalOldItems'], $month['totalOld2Items']);
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
            <?php
            for ($i=0; $i < $totalItems; $i++):
                $monthUuBills = $month['uuBills']; // Универсальные счета за месяц
                $monthUuItems = $month['uuItems']; // Универсальные проводки за месяц

                $monthOldBills = $month['oldBills']; // Старые счета за месяц
                $monthOldItems = $month['oldItems']; // Старые позиции счета за месяц

                $monthOldUUBills = $month['oldUUBills'];
                $monthOldUUItems = $month['oldUUItems'];

                $monthPayments = $month['payments']; // Платежи за месяц

                /**
                 * @var uuBill[] $monthUuBills
                 * @var Bill[] $monthOldBills
                 * @var AccountEntry[] $monthUuItems
                 * @var BillLine[] $monthOldItems
                 * @var Payment[] $monthPayments
                 */
                ?>
                <tr>
                    <?php if (!isset($monthUuBills[$i]) && count($monthUuBills) == $i): ?>
                        <td rowspan="<?= ($totalItems - $i) ?>"></td>
                    <?php elseif (isset($monthUuBills[$i])): ?>
                        <td>
                            <?= Html::a('Счет-фактура № '. $monthUuBills[$i]->id, $monthUuBills[$i]->url, ['target' => '_blank']) ?>
                            от <?=$monthUuBills[$i]->date?> на <?=$monthUuBills[$i]->price?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?php if (isset($monthUuItems[$i])): ?>
                            <abbr title="ID не универсальной услуги"><?= $monthUuItems[$i]->accountTariff->getNonUniversalId() ?></abbr>:
                            <?php
                            switch ($monthUuItems[$i]->type_id) {
                                case AccountEntry::TYPE_ID_PERIOD:
                                    echo '<i>' . $monthUuItems[$i]->getTypeName() . '</i>';
                                    break;
                                default:
                                    echo $monthUuItems[$i]->getTypeName();
                                    break;
                            }
                            ?>
                            (<?= $monthUuItems[$i]->price_with_vat ?>)
                        <?php endif; ?>
                    </td>
                    <?php if (!isset($monthOldUUBills[$i]) && count($monthOldUUBills) == $i): ?>
                        <td rowspan="<?= ($totalItems - $i) ?>"></td>
                    <?php elseif (isset($monthOldUUBills[$i])) :?>
                        <td>
                            <?= Html::a('Счет № ' . $monthOldUUBills[$i]->bill_no, $monthOldUUBills[$i]->url, [
                                'target' => '_blank',
                                'class' => 'bill-info',
                                'data-bill' => $monthOldUUBills[$i]->bill_no,
                            ]) ?> от <?=$monthOldUUBills[$i]->bill_date?> на <?=$monthOldUUBills[$i]->sum?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?php if (isset($monthOldUUItems[$i])): ?>
                            <div data-bill="<?= $monthOldUUItems[$i]->bill_no ?>">
                                <abbr title="ID не универсальной услуги"><?= $monthOldUUItems[$i]->item_id ?></abbr>:
                                <?php if ($monthOldUUItems[$i]->date_from !== $monthOldUUItems[$i]->bill->bill_date): ?>
                                    Плата за услугу
                                <?php elseif ((new DateTime($monthOldUUItems[$i]->date_from))->format('Y-m') == $monthKey): ?>
                                    <i>Абонентка</i>
                                <?php endif; ?>
                                (<?= $monthOldUUItems[$i]->sum ?>)
                            </div>
                        <?php endif; ?>
                    </td>

                    <?php if (!isset($monthOldBills[$i]) && count($monthOldBills) == $i): ?>
                        <td rowspan="<?= ($totalItems - $i) ?>"></td>
                    <?php elseif (isset($monthOldBills[$i])) :?>
                        <td>
                            <?= Html::a('Счет № ' . $monthOldBills[$i]->bill_no, $monthOldBills[$i]->url, [
                                'target' => '_blank',
                                'class' => 'bill-info',
                                'data-bill' => $monthOldBills[$i]->bill_no,
                            ]) ?> от <?=$monthOldBills[$i]->bill_date?> на <?=$monthOldBills[$i]->sum?>
                        </td>
                    <?php endif; ?>
                    <td>
                        <?php if (isset($monthOldItems[$i])): ?>
                            <div data-bill="<?= $monthOldItems[$i]->bill_no ?>">
                                <abbr title="ID не универсальной услуги"><?= $monthOldItems[$i]->id_service ?></abbr>:
                                <?php if ($monthOldItems[$i]->date_from !== $monthOldItems[$i]->bill->bill_date): ?>
                                    Плата за услугу
                                <?php elseif ((new DateTime($monthOldItems[$i]->date_from))->format('Y-m') == $monthKey): ?>
                                    <i>Абонентка</i>
                                <?php endif; ?>
                                (<?= $monthOldItems[$i]->sum ?>)
                            </div>
                        <?php endif; ?>
                    </td>
                    <?php if (!isset($monthPayments[$i]) && count($monthPayments) == $i): ?>
                        <td rowspan="<?= ($totalItems - $i) ?>"></td>
                    <?php elseif (isset($monthPayments[$i])): ?>
                        <td>
                            <?= $monthPayments[$i]->paymentDate ?>
                            (<?= $monthPayments[$i]->paymentSum ?>)
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
                $('div[data-bill="' + $(this).data('bill') + '"]').css('background-color', '#D0D0D0');
            },
            function() {
                $('div[data-bill="' + $(this).data('bill') + '"]').css('background-color', 'inherit');
            }
        )
});
</script>