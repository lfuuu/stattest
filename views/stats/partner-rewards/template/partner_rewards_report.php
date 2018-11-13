<?php
/** @var PartnerRewardsFilter $filterModel*/

use app\models\ClientContract;
use app\models\filter\PartnerRewardsFilter;

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
            <td>Сумма оплаченных счетов</td>
            <td>Сумма оплаченных услуг, за которые начисленно вознаграждение</td>
            <td colspan="5">Сумма вознаграждения</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Разовое</td>
            <td>% от подключения</td>
            <td>% от абонентской платы</td>
            <td>% от превышения</td>
            <td>% от маржи</td>
        </tr>
        <tr>
            <td colspan="2"><b>ИТОГО</b></td>
            <?php foreach($keys as $key) : ?>
                <td><b><?= PartnerRewardsFilter::getNumberFormat($summary[$key]); ?></b></td>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach($dataProvider->allModels as $model) : ?>
            <tr>
                <td><?= $model['contragent_name']; ?></td>
                <td><?= $model['client_created']; ?></td>
                <?php foreach($keys as $key) : ?>
                    <td><?= PartnerRewardsFilter::getNumberFormat($model[$key]); ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="margin-top: 30px;">
    <b>Итого по начисленному вознаграждению: <?= PartnerRewardsFilter::getTotalSummary($filterModel); ?> руб.</b>
</div>
<br>
<div>
    <div style="width: 50%; float: left;">Оператор __________________________________/ ___________ /</div>
    <div style="width: 50%; float: left;">Агент __________________________________/ ___________ /</div>
</div>