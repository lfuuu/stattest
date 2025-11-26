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
        <td colspan="2" class="s" align="center">Сведения о посреднике (комиссионере, агенте)</td>
        <td rowspan="2" class="s" align="center">Номер и дата документа, подтверждаю-щего оплату</td>
        <td rowspan="2" class="s" align="center">Наимено-вание <br/>и<br/>код валюты</td>
        <td colspan="2" class="s" align="center">
            Стоимость продаж по счету-фактуре, разница стоимости по
            корректиро- вочному счету-фактуре (включая НДС) в валюте
            счета-фактуры
        </td>
        <td colspan="6" class="s">
            Стоимость продаж, облагаемых налогом, по
            счету-фактуре, разница стоимости по корректировочному
            счету-фактуре (без НДС) в рублях и
            копейках по ставке
        </td>
        <td colspan="5" class="s">
            Сумма НДС по счету-фактуре,
            разница суммы налога по корректиро-вочному
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
            НДС 0% (агент)
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
    <td class="s" align="center">7 про-цен-тов</td>
    <td class="s" align="center">5 про-цен-тов</td>
    <td class="s" align="center">0 про-цен-тов</td>
    <td class="s" align="center">20 про-цен-тов</td>
    <td class="s" align="center">18 про-цен-тов</td>
    <td class="s" align="center">10 про-цен-тов</td>
    <td class="s" align="center">7 про-цен-тов</td>
    <td class="s" align="center">5 про-цен-тов</td>
    </thead>
    <tbody>
    <?php

    use app\helpers\DateTimeZoneHelper;
    use app\models\ClientContragent;
    use app\models\Invoice;
    use app\modules\uu\models\ServiceType;
    use app\helpers\SaleBookHelper;

        $query = $filter->search();
    //if($query){
    //$query->andWhere(['inv.number' => ['1251001-0984', '2250901-3329', '1251101-0668', '1251001-1088','1251001-0984','1251101-0668']]);
    //$query->limit(10);
    //}

    $idx = 1;

    $total = [
        'sumAll' => 0, 'sum20' => 0, 'sum18' => 0, 'sum10' => 0, 'sum7' => 0, 'sum5' => 0, 'sum0' => 0, 'sum0_agent' => 0,
        'tax20' => 0, 'tax18' => 0, 'tax10' => 0, 'tax7' => 0, 'tax5' => 0, 'tax' => 0,
        'sumCol16' => 0, 'sumTax20' => 0
    ];

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

//                $taxRate = $account->getTaxRate();

                $sum = $invoice->sum;
                $sum_without_tax = $invoice->type_id != Invoice::TYPE_PREPAID ? $invoice->sum_without_tax : null;
                $sum_tax = $invoice->sum_tax;

                $linesSum16 = 0;
                $linesTax20 = 0;
                $linesData = [
                    'sumCol16' => 0,
                    'sum20' => 0, 'tax20' => 0,
                    'sum18' => 0, 'tax18' => 0,
                    'sum10' => 0, 'tax10' => 0,
                    'sum7' => 0, 'tax7' => 0,
                    'sum5' => 0, 'tax5' => 0,
                    'sum0' => 0, 'tax0' => 0,
                    'sum0_agent' => 0,
                ];

                $lt20 = 0;

                                foreach ($invoice->lines as $line) {
                    if ($line->sum < 0) {
                        continue;
                    }

                    $taxRate = (int)$line->tax_rate;
                    //                    $linesSum16 += $line['sum_tax'] > 0 ? $line['sum'] : 0;
                    $l16 = $line['sum_tax'] > 0 ? $line['sum'] : 0;
                    //                    $linesTax20 += abs($line['sum_tax']) > 0 ? $line['sum_without_tax'] : 0;
                    $lt20 = abs($line['sum_tax']) > 0 ? $line['sum_without_tax'] : 0;

                    $isVatsTs = SaleBookHelper::isTelephonyService($line);

                    if ($taxRate === 0) {
                        if ($isVatsTs) {
                            if (!isset($linesData['sum0'])) {
                                $linesData['sum0'] = 0;
                                $linesData['tax0'] = 0;
                            }
                            $linesData['sum0'] += $line->sum_without_tax;
                            $linesData['tax0'] += $line->sum_tax;

                            $total['sum0'] += $line->sum_without_tax;
                            $total['tax0'] = ($total['tax0'] ?? 0) + $line->sum_tax;
                        } else {
                            $linesData['sum0_agent'] += $line->sum_without_tax;
                            $total['sum0_agent'] += $line->sum_without_tax;
                        }
                    } else {
                        if (!isset($linesData['sum' . $taxRate])) {
                            $linesData['sum' . $taxRate] = 0;
                            $linesData['tax' . $taxRate] = 0;
                        }

                        $linesData['sum' . $taxRate] += $line->sum_without_tax;
                        $linesData['tax' . $taxRate] += $line->sum_tax;

                        $total['sum' . $taxRate] += $line->sum_without_tax;
                        $total['tax' . $taxRate] += $line->sum_tax;
                    }

                    if ($line->sum_tax > 0) {
                        $linesData['sumCol16'] += $line->sum;
                    }
                }
                $total['sumCol16'] += $linesData['sumCol16'];
                $total['sumTax20'] += $lt20 ?: 0;
            } catch (Exception $e) {
                Yii::$app->session->addFlash('error', $e->getMessage());
                continue;
            }

            $total['sumAll'] += $sum;
//            $sum_without_tax && $total['sum' . $taxRate] += $sum_without_tax;
//            $total['tax' . $taxRate] += $sum_tax;
//            $total['sumCol16'] += $linesSum16 ?: 0;
//            $total['sumTax20'] += $linesTax20 ?: 0;

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
                <td><?= $linesData['sum20'] ? $printSum($linesData['sum20']) : '&nbsp;' ?></td>
                <td><?= $linesData['sum18'] ? $printSum($linesData['sum18']) : '&nbsp;' ?></td>
                <td><?= $linesData['sum10'] ? $printSum($linesData['sum10']) : '&nbsp;' ?></td>
                <td><?= $linesData['sum7'] ? $printSum($linesData['sum7']): '&nbsp;' ?></td>
                <td><?= $linesData['sum5'] ? $printSum($linesData['sum5']) : '&nbsp;' ?></td>
                <td><?= $linesData['sum0'] ? $printSum($linesData['sum0']): '&nbsp;' ?></td>
                <td><?= $linesData['tax20'] ? $printSum($linesData['tax20']) : '&nbsp;' ?></td>
                <td><?= $linesData['tax18'] ? $printSum($linesData['tax18']) : '&nbsp;' ?></td>
                <td><?= $linesData['tax10'] ? $printSum($linesData['tax10']) : '&nbsp;' ?></td>
                <td><?= $linesData['tax7'] ? $printSum($linesData['tax7']) : '&nbsp;' ?></td>
                <td><?= $linesData['tax5'] ? $printSum($linesData['tax5']) : '&nbsp;' ?></td>
                <td>&nbsp;</td>
                <td><?= $linesData['sum0_agent'] ? $printSum($linesData['sum0_agent']) : '&nbsp;' ?></td>
                <td><?= ($linesData['sumCol16'] ?? 0) > 0 ? $printSum($linesData['sumCol16']) : "" ?></td>
            </tr>
        <?php
        endforeach;
    ?>
    <tr class="even">
        <td colspan="14" align="right">Всего:</td>
        <td><?= $account->currency == 'RUB' ? " " : $printSum($sum) ?></td>
        <td><?= $printSum($total['sumAll']) ?></td>
        <td><?= $printSum($total['sum20']) ?></td>
        <td><?= $printSum($total['sum18']) ?></td>
        <td><?= $printSum($total['sum10']) ?></td>
        <td><?= $printSum($total['sum7']) ?></td>
        <td><?= $printSum($total['sum5']) ?></td>
        <td><?= $printSum($total['sum0']) ?></td>
        <td><?= $printSum($total['tax20']) ?></td>
        <td><?= $printSum($total['tax18']) ?></td>
        <td><?= $printSum($total['tax10']) ?></td>
        <td><?= $printSum($total['tax7']) ?></td>
        <td><?= $printSum($total['tax5']) ?></td>
        <td>&nbsp;</td>
        <td><?= $printSum($total['sum0_agent']) ?></td>
        <td><?= $printSum($total['sumCol16']) ?></td>
    </tr>
    </tbody>
</table>
