<?php

use app\commands\UbillerController;
use app\models\Bill;
use app\models\ClientAccount;
use app\modules\uu\models\Bill as uuBill;
use app\modules\uu\tarificator\RealtimeBalanceTarificator;
use yii\widgets\Breadcrumbs;

/**
 * Бухгалтерский баланс
 *
 * @var \app\classes\BaseView $this
 * @var ClientAccount $clientAccount
 * @var Currency $currency
 * @var Payment[] $payments
 * @var uuBill[] $uuBills
 * @var Bill[] $billsUsage
 * @var Bill[] $billsUniversal
 * @var array $accountEntrySummary
 * @var array $paymentSummary
 * @var array $uuBillSummary
 */

$params = [
    'clientAccount' => $clientAccount,
    'currency' => $currency,
    'payments' => $payments,
    'uuBills' => $uuBills,
    'billsUsage' => $billsUsage,
    'billsUniversal' => $billsUniversal,
    'accountEntrySummary' => $accountEntrySummary,
    'paymentSummary' => $paymentSummary,
    'uuBillSummary' => $uuBillSummary,
];
?>

<?= Breadcrumbs::widget([
    'links' => [
        [
            'label' => Yii::t('tariff', 'Universal tarifficator') .
                $this->render('//layouts/_helpConfluence', UbillerController::getHelpConfluence()),
            'encode' => false,
        ],

        $this->title = Yii::t('tariff', 'Balance'),
        [
            'label' => $this->render('//layouts/_helpConfluence', RealtimeBalanceTarificator::getHelpConfluence()),
            'encode' => false,
        ],
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
