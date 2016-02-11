<?php

use yii\helpers\Url;

$amount = 0;
$amountIsPayed = 0;
$oncet = 0;
$fee = 0;
$excess = 0;
?>

<form>
<div class="row">

    <div class="col-sm-12">
        <h2>Отчет по партнерам (агентам)</h2>
    </div>


    <div class="col-sm-12">
        <div class="row form-group">
            <div class="col-sm-2"><label>Партнер</label></div>
            <div class="col-sm-3">
                <?=
                \yii\helpers\Html::dropDownList(
                    'partner_contract_id',
                    \Yii::$app->request->get('partner_contract_id', 0),
                    $partnerList,
                    [
                        'class' => 'select2',
                        'style' => 'width: 100%;'
                    ]
                )
                ?>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row form-group">
            <div class="col-sm-2"><label>Период отчета</label></div>
            <div class="col-sm-3">
                <?= \kartik\daterange\DateRangePicker::widget([
                    'name' => 'date',
                    'value' => $dateFrom && $dateTo ? "$dateFrom - $dateTo" : '',
                    'pluginOptions' => [
                        'format' => 'YYYY-MM-DD'
                    ]
                ]) ?>
            </div>
            <div class="col-sm-2">
                <input type="submit" class="form-control" value="Показать"/>
            </div>
        </div>
    </div>


    <div class="col-sm-2">
        <input type="submit" class="form-control" name="exportToCSV" value="Экспорт в CSV"/>
    </div>

    <div class="col-sm-12">
        Договор № <?= $partner->contract->number ?> ЛС № <?= $partner->id ?>
        <br/>
        Партнер: <b><?= $partner->contragent->name ?></b>
        <br/>
        Расчетный период с <?= $dateFrom ?> по <?= $dateTo ?>
    </div>

    <div style="float: left;margin-left: 100px; margin-top: 10px; padding: 0 5px 0 5px;" class="bg-danger">
        <?php if ($contractsWithoutReward) { ?>
            <h2 style="padding: 0; margin: 3px 0 3px 0;">Отсутствуют настройки вознаграждений для договоров:</h2>
            <ul>
            <?php foreach($contractsWithoutReward as $contract) { ?>
                <li><a href="<?=Url::to(["contract/edit", "id" => $contract['id']])?>"><?=$contract['name']?> (#<?=$contract['account_id']?>)</a></li>
            <?php } ?>
            </ul>
        <?php } ?>
    </div>

    <div style="float: left;margin-left: 100px; margin-top: 10px; padding: 0 5px 0 5px;" class="bg-danger">
        <?php if ($contractsWithIncorrectBP) { ?>
            <h2 style="padding: 0; margin: 3px 0 3px 0;">Договора с неправильным бизнес-процессом:</h2>
            <ul>
            <?php foreach($contractsWithIncorrectBP as $contract) { ?>
                <li><a href="<?=Url::to(["contract/edit", "id" => $contract['id']])?>"><?=$contract['name']?> (#<?=$contract['account_id']?>)</a></li>
            <?php } ?>
            </ul>
        <?php } ?>
    </div>

</div>
</form>
<div class="row">
    <div class="col-sm-12">
        <h3>Отчет по подключенным клиентам</h3>
    </div>

    <div class="col-sm-12">
        <table class="report">
            <thead style="background: lightyellow">
            <tr>
                <th rowspan="2">Наименование клиента</th>
                <th rowspan="2">Дата регистрации клиента</th>
                <th rowspan="2">Услуга</th>
                <th rowspan="2">Тариф</th>
                <th rowspan="2">Дата включения услуги</th>
                <th rowspan="2">Сумма оказанных услуг</th>
                <th rowspan="2">Сумма оплаченных услуг</th>
                <th colspan="3">Сумма вознаграждения</th>
            </tr>
            <tr>
                <th>Разовое</th>
                <th>% от абонентской платы</th>
                <th>% от превышения</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $line): ?>
                <?php
                $amount += $line['amount'];
                $amountIsPayed += $line['amountIsPayed'];
                $oncet += $line['once'];
                $fee += $line['fee'];
                $excess += $line['excess'];
                ?>

                <tr>
                    <td><a href="/client/view?id=<?= $line['id'] ?>"><?= $line['name'] ?></a></td>
                    <td><?= $line['created'] ?></td>
                    <td><?= ($line['usage'] == 'voip' ? 'Телефония' : ($line['usage'] == 'vpbx' ? 'ВАТС' : '')) ?></td>
                    <td><?= $line['tariffName'] ?></td>
                    <td><?= $line['activationDate'] ?></td>
                    <td><?= number_format($line['amount'], 2) ?></td>
                    <td><?= number_format($line['amountIsPayed'], 2) ?></td>
                    <td><?= number_format($line['once'], 2) ?></td>
                    <td><?= number_format($line['fee'], 2) ?></td>
                    <td><?= number_format($line['excess'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot style="background: lightyellow">
            <tr>
                <td><b>Итого</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?= number_format($amount, 2) ?></td>
                <td><?= number_format($amountIsPayed, 2) ?></td>
                <td><?= number_format($oncet, 2) ?></td>
                <td><?= number_format($fee, 2) ?></td>
                <td><?= number_format($excess, 2) ?></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
    .report td, .report th {
        text-align: center;
        padding: 5px;
        font-size: 11px;
    }

    .report tbody tr:nth-child(2n) {
        background: #eee;
    }
</style>
