<?php
/** @var PartnerRewardsNewFilter $filterModel*/

use app\models\ClientContract;
use app\models\filter\PartnerRewardsNewFilter;

$keys = [
    'paid_summary', 'paid_summary_reward', 'once', 'percentage_once',
    'percentage_of_fee', 'percentage_of_over', 'percentage_of_margin'
];
$dataProvider = $filterModel->search();
$summary = $filterModel->summary;

$partnerName = "#".$filterModel->partner_contract_id;
if ($contract = ClientContract::findOne(['id' => $filterModel->partner_contract_id])) {
    $partnerName = $contract->contragent->name;
}

?>

<b>Отчет по партнерскому вознаграждению</b>
<br>
<span>Агент:
    <b><?= $partnerName ?></b>
</span>
<br>
<span>Расчетный период за <?= $filterModel->payment_date_before ?> - <?= $filterModel->payment_date_after ?> г.</span>
<br>
<table border="1" style="margin-top: 30px; border: 1px solid black;border-collapse: collapse;">
    <thead style="text-align: center;">
        <tr>
            <td>Наименование клиента</td>
            <td>Дата регистрации клиента</td>
            <td width="15%">Сумма оплаченных счетов</td>
            <td width="15%">Сумма оплаченных услуг, за которые начисленно вознаграждение</td>
            <td width="15%">Сумма вознаграждения</td>
        </tr>
        <tr>
            <td colspan="2"><b>ИТОГО</b></td>
            <td><b><?= PartnerRewardsNewFilter::getNumberFormat($summary['paid_summary']); ?></b></td>
            <td><b><?= PartnerRewardsNewFilter::getNumberFormat($summary['paid_summary_reward']); ?></b></td>
            <td><b><?= PartnerRewardsNewFilter::getNumberFormat($summary['sum']); ?></b></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach($dataProvider->allModels as $model) : ?>
            <tr>
                <td><?= $model['contragent_name']; ?></td>
                <td><?= $model['client_created']; ?></td>
                <td><?= PartnerRewardsNewFilter::getNumberFormat($model['paid_summary']); ?></td>
                <td><?= PartnerRewardsNewFilter::getNumberFormat($model['paid_summary_reward']); ?> </td>
                <td><?= PartnerRewardsNewFilter::getNumberFormat($model['sum']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="margin-top: 30px;">
    <b>Итого по начисленному вознаграждению: <?= PartnerRewardsNewFilter::getNumberFormat($filterModel->summary['sum']); ?> руб.</b>
</div>
<br>
<div>
    <div style="width: 50%; float: left;">Оператор __________________________________/ ___________ /</div>
    <div style="width: 50%; float: left;">Агент __________________________________/ ___________ /</div>
</div>