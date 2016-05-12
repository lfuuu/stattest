<?php
/**
 * Бухгалтерский баланс. Realtime баланс
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

use app\classes\Html;
use app\classes\uu\model\AccountEntry;
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
        <td><?= $accountLogSetupSummary['total_count'] + $accountLogPeriodSummary['total_count'] + $accountLogResourceSummary['total_count'] ?> шт.</td>
        <td>
                    <span class="uu-balance-toggle-offset">
                        <?= sprintf('%+.2f', -($transactionSum = $accountLogSetupSummary['total_price'] + $accountLogPeriodSummary['total_price'] + $accountLogResourceSummary['total_price'])) ?>
                    </span>
            <?= $currency->symbol ?>
        </td>
    </tr>
    <tr class="uu-balance-offset" style="display: none;">
        <td>... за подключение</td>
        <td><?= $accountLogSetupSummary['total_count'] ?> шт.</td>
        <td>
            <?= Html::a(
                sprintf('%+.2f', -$accountLogSetupSummary['total_price']),
                Url::to(['uu/account-log/setup', 'AccountLogSetupFilter[client_account_id]' => $clientAccount->id])
            ) ?>
            <?= $currency->symbol ?>
        </td>
    </tr>
    <tr class="uu-balance-offset" style="display: none;">
        <td>... за абонентку</td>
        <td><?= $accountLogPeriodSummary['total_count'] ?> шт.</td>
        <td>
            <?= Html::a(
                sprintf('%+.2f', -$accountLogPeriodSummary['total_price']),
                Url::to(['uu/account-log/period', 'AccountLogPeriodFilter[client_account_id]' => $clientAccount->id])
            ) ?>
            <?= $currency->symbol ?>
        </td>
    </tr>
    <tr class="uu-balance-offset" style="display: none;">
        <td>... за ресурсы</td>
        <td><?= $accountLogResourceSummary['total_count'] ?> шт.</td>
        <td>
            <?= Html::a(
                sprintf('%+.2f', -$accountLogResourceSummary['total_price']),
                Url::to(['uu/account-log/resource', 'AccountLogResourceFilter[client_account_id]' => $clientAccount->id])
            ) ?>
            <?= $currency->symbol ?>
        </td>
    </tr>
    <tr>
        <td>Баланс</td>
        <td></td>
        <td>
            <b><?= sprintf('%+.2f', $paymentSummary['total_price'] - $transactionSum) ?></b> <?= $currency->symbol ?>
        </td>
    </tr>
    </tbody>
</table>


<?php // скрыть/показать  детальные транзакции ?>
<script type='text/javascript'>
    $(function () {
        $('.uu-balance-toggle-offset').on('click', function () {
            $('.uu-balance-offset').slideToggle(function () {
                if (window.reflowTableHeader) {
                    window.reflowTableHeader();
                }
            });
        });
    });
</script>
