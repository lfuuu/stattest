<?php
/**
 * Бухгалтерский баланс. Бухгалтерский баланс
 *
 * @var \yii\web\View $this
 * @var ClientAccount $clientAccount
 * @var \app\models\Currency $currency
 * @var AccountEntry[] $accountEntries
 * @var Payment[] $payments
 * @var array $accountEntrySummary
 * @var array $accountLogSetupSummary
 * @var array $accountLogPeriodSummary
 * @var array $accountLogResourceSummary
 * @var array $paymentSummary
 */

use app\classes\Html;
use app\classes\uu\model\AccountEntry;
use app\models\ClientAccount;
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
        <td><?= sprintf('%+.2f', $paymentSummary['total_price']) ?> <?= $currency->symbol ?></td>
    </tr>
    <tr>
        <td>Всего проводок</td>
        <td><?= $accountEntrySummary['total_count'] ?> шт.</td>
        <td>
            <?= Html::a(
                sprintf('%+.2f', -$accountEntrySummary['total_price']),
                Url::to(['uu/account-entry', 'AccountEntryFilter[client_account_id]' => $clientAccount->id])
            ) ?>
            <?= $currency->symbol ?>
        </td>
    </tr>
    <tr>
        <td>Баланс</td>
        <td></td>
        <td><b><?= sprintf('%+.2f', $paymentSummary['total_price'] - $accountEntrySummary['total_price']) ?></b> <?= $currency->symbol ?></td>
    </tr>
    </tbody>
</table>
