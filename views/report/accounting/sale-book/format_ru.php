<?php

/** @var \app\models\filter\SaleBookFilter $filter */

?>
<h2>Книга продаж</h2>
<table class="price" cellspacing="4" cellpadding="2" border="1"
       style="border-collapse: collapse; font: normal 8pt sans-serif; padding: 2px 2px 2px 2px;">

    <thead>
    <tr>
        <td rowspan="2" class="s" align="center">№ п/п</td>
        <td rowspan="2" class="s" align="center">Код вида операции</td>
        <td rowspan="2" class="s" align="center">Номер и дата счета-фактуры продавца</td>
        <td rowspan="2" class="s" align="center">Регистрационный номер таможенной декларации</td>
        <td rowspan="2" class="s" align="center">Код вида товара</td>
        <td rowspan="2" class="s" align="center">Номер и дата исправ-ления счета-факту-ры про-давца</td>
        <td rowspan="2" class="s" align="center">Номер и дата корректи-ровочного счета-фактуры продавца</td>
        <td rowspan="2" class="s" align="center">Номер и дата исправ-ления корректи-ровочного счета-фактуры продавца
        </td>
        <td rowspan="2" class="s" align="center">Наименование покупателя</td>
        <td rowspan="2" class="s" align="center">ИНН/КПП<br/>покупателя</td>
        <td colspan="2" class="s" align="center">Сведения о посреднике (комиссио-нере, агенте)</td>
        <td rowspan="2" class="s" align="center">Номер и дата документа, подтверждаю-щего оплату</td>
        <td rowspan="2" class="s" align="center">Наимено-вание <br/>и<br/>код валюты</td>
        <td colspan="2" class="s" align="center">
            Стоимость продаж по счету-фактуре, разница стоимости по
            корректировочному счету-фактуре (включая НДС) в валюте
            счета-фактуры
        </td>
        <td colspan="4" class="s">
            Стоимость продаж, облагаемых налогом, по
            счету-фактуре, разница стоимости по корректировочному
            счету-фактуре<br/>(без НДС) в рублях и
            копейках по ставке
        </td>
        <td colspan="3" class="s">
            Сумма НДС по счету-фактуре,
            разница суммы налога по корректировочному
            счету-фактуре в рублях и копейках, по ставке
        </td>
        <td rowspan="2" class="s" align="center">
            Стоимость
            продаж,
            освобождаемых
            от налога, по
            счету-фактуре,
            разница
            стоимости
            по корректиро-
            вочному
            счету-фактуре
            в рублях и
            копейках
        </td>
        <td rowspan="2" class="s" align="center">
            Сумма <br/>
            С/Ф с НДС
        </td>
    </tr>
    <td class="s" align="center">наименова-ние посред-ника</td>
    <td class="s" align="center">ИНН/КПП посредника</td>
    <td class="s" align="center">в валюте счета-фактуры</td>
    <td class="s" align="center">в рублях и копейках</td>
    <td class="s" align="center">20 про-цен-тов</td>
    <td class="s" align="center">18 про-цен-тов</td>
    <td class="s" align="center">10 про-цен-тов</td>
    <td class="s" align="center">0 про-цен-тов</td>
    <td class="s" align="center">20 про-цен-тов</td>
    <td class="s" align="center">18 про-цен-тов</td>
    <td class="s" align="center">10 про-цен-тов</td>
    </thead>
    <tbody>
    <?php

    use app\helpers\DateTimeZoneHelper;
    use app\models\ClientContragent;
    use app\models\Invoice;

    $query = $filter->search();

    $idx = 1;

    $total = ['sumAll' => 0, 'sum20' => 0, 'sum18' => 0, 'sum10' => 0, 'sum0' => 0, 'tax20' => 0, 'tax18' => 0, 'tax10' => 0, 'tax' => 0, 'sumCol16' => 0];

    if ($query)
        foreach ($query->each() as $invoice) : ?>
            <?php /** @var \app\models\filter\SaleBookFilter $invoice */

//            if (!$filter->check($invoice)) {
//                continue;
//            }

            if (!$invoice->bill || !$invoice->bill->clientAccount) {
                Yii::$app->session->addFlash('error', 'С/ф без счета: ' . $invoice->number);
                continue;
            }
            try {
                $account = $invoice->bill->clientAccount;
                $contract = $account->contract;
                $contragent = $contract->contragent;

                $taxRate = $account->getTaxRate();

                $sum = $invoice->sum;
                $sum_without_tax = $invoice->type_id != Invoice::TYPE_PREPAID ? $invoice->sum_without_tax : null;
                $sum_tax = $invoice->sum_tax;

                $linesSum16 = 0;
                $linesTax20 = 0;
                foreach ($invoice->lines as $line) {
                    $linesSum16 += $line['sum_tax'] > 0 ? $line['sum'] : 0;
                    $linesTax20 += abs($line['sum_tax']) > 0 ? $line['sum_without_tax'] : 0;
                }
            } catch (Exception $e) {
                Yii::$app->session->addFlash('error', $e->getMessage());
                continue;
            }

            $total['sumAll'] += $sum;
            $sum_without_tax && $total['sum' . $taxRate] += $sum_without_tax;
            $total['tax' . $taxRate] += $sum_tax;
            $total['sumCol16'] += $linesSum16 ?: 0;  
            $total['sumTax20'] += $linesTax20 ?: 0;

            ?>
            <tr class="<?= ($idx % 2 == 0 ? 'odd' : 'even') ?>">
                <td><?= ($idx++) ?> </td>
                <td><?= ($invoice->type_id == Invoice::TYPE_PREPAID ? '02' : '01') ?></td>
                <td><?= ($invoice->number . '; ' . $invoice->getDateImmutable()->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED)) ?></td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td><?= ($invoice->correction_idx ? $invoice->correction_idx . '; ' . $invoice->getDateImmutable()->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) : '---') ?></td>
                <td>---</td>
                <td>---</td>
                <td><?= trim($contragent->name_full) ?></td>
                <td><?= trim($contragent->inn) ?>
                    <?= ($contragent->legal_type == ClientContragent::LEGAL_TYPE ? '/' . (trim($contragent->kpp) ?: '') : '') ?></td>
                <td>---</td>
                <td>---</td>
                <td><?= $invoice->getPaymentsStr() ?: '&nbsp;' ?></td>
                <td><?= $account->currency == 'RUB' ? ' ' : $account->currencyModel->name. ' ' . $account->currencyModel->code ?></td>
                <td><?= $account->currency == 'RUB' ? " " : $printSum($sum) ?></td>
                <td><?= $printSum($sum) ?></td>
                <td><?= $sum_without_tax !== null && $taxRate == 20 ? $printSum($linesTax20) : '&nbsp;' ?></td>
                <td><?= $taxRate == 18 ? $printSum($sum_without_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 10 ? $printSum($sum_without_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 0 ? $printSum($sum_without_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 20 ? $printSum($sum_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 18 ? $printSum($sum_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 10 ? $printSum($sum_tax) : '&nbsp;' ?></td>
                <td>&nbsp;</td>
                <td><?= $sum_tax > 0 ? $printSum($linesSum16) : "" ?></td>
            </tr>
        <?php
        endforeach;
    ?>
    <tr class="even">
        <td colspan="14" align="right">Всего:</td>
        <td><?= $account->currency == 'RUB' ? " " : $printSum($sum) ?></td>
        <td><?= $printSum($total['sumAll']) ?></td>
        <td><?= $printSum($total['sumTax20']) ?></td>
        <td><?= $printSum($total['sum18']) ?></td>
        <td><?= $printSum($total['sum10']) ?></td>
        <td><?= $printSum($total['sum0']) ?></td>
        <td><?= $printSum($total['tax20']) ?></td>
        <td><?= $printSum($total['tax18']) ?></td>
        <td><?= $printSum($total['tax10']) ?></td>
        <td>&nbsp;</td>
        <td><?= $printSum($total['sumCol16']) ?></td>
    </tr>
    </tbody>
</table>
