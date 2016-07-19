<?php
/**
 * Бухгалтерский баланс
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

use app\classes\uu\model\AccountEntry;
use app\models\Bill;
use app\models\ClientAccount;
use yii\widgets\Breadcrumbs;

$params = [
    'clientAccount' => $clientAccount,
    'currency' => $currency,
    'accountEntries' => $accountEntries,
    'payments' => $payments,
    'accountBills' => $accountBills,
    'bills' => $bills,
    'accountEntrySummary' => $accountEntrySummary,
    'accountLogSetupSummary' => $accountLogSetupSummary,
    'accountLogPeriodSummary' => $accountLogPeriodSummary,
    'accountLogResourceSummary' => $accountLogResourceSummary,
    'paymentSummary' => $paymentSummary,
];
?>

<?= Breadcrumbs::widget([
    'links' => [
        Yii::t('tariff', 'Universal tarifficator'),
        $this->title = Yii::t('tariff', 'Balance'),
    ],
]) ?>

<?php
if (!$clientAccount) {
    Yii::$app->session->setFlash('error', Yii::t('tariff', 'You should {a_start}select a client first{a_finish}', ['a_start' => '<a href="/">', 'a_finish' => '</a>']));
    return;
}
?>

<div class="row">

    <div class="col-sm-5">
        <?= $this->render('_account_balance', $params) ?>
    </div>

    <div class="col-sm-offset-2 col-sm-5">
        <?= $this->render('_realtime_balance', $params) ?>
    </div>
</div>


<?= $this->render('_grid', $params) ?>
