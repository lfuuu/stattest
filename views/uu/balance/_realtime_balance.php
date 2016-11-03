<?php
/**
 * Бухгалтерский баланс. Realtime баланс
 *
 * @var \yii\web\View $this
 * @var ClientAccount $clientAccount
 * @var Currency $currency
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
        <td><?= sprintf('%+.2f', $paymentSummary['total_price']) ?> <?= $currency->symbol ?></td>
    </tr>
    <tr>
        <td>Всего транзакций</td>
        <td></td>
        <td>
            <?= Html::a(
                sprintf('%+.2f', -$uuBillSummary['total_price']),
                Url::to(['uu/bill', 'AccountBillFilter[client_account_id]' => $clientAccount->id])
            ) ?>
            <?= $currency->symbol ?>
        </td>
    </tr>
    <tr>
        <td>Баланс</td>
        <td></td>
        <td>
            <b><?= sprintf('%+.2f', $clientAccount->billingCounters->getRealtimeBalance()) ?></b> <?= $currency->symbol ?>
        </td>
    </tr>
    </tbody>
</table>
