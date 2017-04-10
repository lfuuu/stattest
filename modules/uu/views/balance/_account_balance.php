<?php
/**
 * Бухгалтерский баланс. Бухгалтерский баланс
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
use app\models\Bill;
use app\models\ClientAccount;
use app\modules\uu\models\AccountEntry;
use yii\helpers\Url;

?>
<table class="table table-hover">
    <thead>
    <tr class="info">
        <th colspan="3">Бухгалтерский баланс</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>Всего платежей</td>
        <td><?= $paymentSummary['total_count'] ?> шт.</td>
        <td>+<?= $currency->format($paymentSummary['total_price']) ?></td>
    </tr>
    <tr>
        <td>Всего проводок</td>
        <td><?= $accountEntrySummary['total_count'] ?> шт.</td>
        <td>
            <?= Html::a(
                $currency->format(-$accountEntrySummary['total_price']),
                Url::to(['/uu/account-entry', 'AccountEntryFilter[client_account_id]' => $clientAccount->id])
            ) ?>
        </td>
    </tr>
    <tr>
        <td>Баланс</td>
        <td></td>
        <td><b><?= $currency->format($paymentSummary['total_price'] - $accountEntrySummary['total_price']) ?></b></td>
    </tr>
    </tbody>
</table>
