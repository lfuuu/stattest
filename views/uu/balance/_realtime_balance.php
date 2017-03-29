<?php
/**
 * Бухгалтерский баланс. Realtime баланс
 *
 * @var \app\classes\BaseView $this
 * @var ClientAccount $clientAccount
 * @var \app\models\Currency $currency
 * @var AccountEntry[] $accountEntries
 * @var Payment[] $payments
 * @var Bill[] $bills
 * @var array $accountEntrySummary
 * @var array $paymentSummary
 * @var array $uuBillSummary
 */

use app\classes\Html;
use app\classes\uu\model\AccountEntry;
use app\models\Bill;
use app\models\ClientAccount;
use yii\helpers\Url;

?>

<table class="table table-hover">
    <thead>
    <tr class="info">
        <th colspan="3">Realtime баланс</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Всего платежей</td>
        <td><?= $paymentSummary['total_count'] ?> шт.</td>
        <td>+<?= $currency->format($paymentSummary['total_price']) ?></td>
    </tr>
    <tr>
        <td>Всего транзакций</td>
        <td></td>
        <td>
            <?= Html::a(
                $currency->format(-$uuBillSummary['total_price']),
                Url::to(['uu/bill', 'AccountBillFilter[client_account_id]' => $clientAccount->id])
            ) ?>
        </td>
    </tr>
    <tr>
        <td>Баланс</td>
        <td></td>
        <td><b><?= $currency->format($clientAccount->billingCounters->getRealtimeBalance()) ?></b></td>
    </tr>
    </tbody>
</table>
