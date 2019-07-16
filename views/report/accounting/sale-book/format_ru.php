<?php

/** @var \app\models\filter\SaleBookFilter $filter */

?>
<h2>Книга продаж</h2>
<table class="price" cellspacing="4" cellpadding="2" border="1"
       style="border-collapse: collapse; font: normal 8pt sans-serif; padding: 2px 2px 2px 2px;">

    <thead>
    <tr>
        <td rowspan="2" class="s">№ п/п</td>
        <td rowspan="2" class="s">Код вида опера&shy;ции</td>
        <td rowspan="2" class="s">Дата и номер счета- факту&shy;ры про&shy;давца</td>
        <td rowspan="2" class="s">Номер и дата испра&shy;вления счета-факту&shy;ры про&shy;дав&shy;ца</td>
        <td rowspan="2" class="s">Номер и дата корректи&shy;ровочного счета-фактуры продавца</td>
        <td rowspan="2" class="s">Номер и дата испра&shy;вления корректи&shy;ровочного счета-фактуры продавца</td>
        <td rowspan="2">Наиме&shy;нова&shy;ние поку&shy;па&shy;те&shy;ля</td>
        <td rowspan="2">ИНН/КПП<br/>поку&shy;пате&shy;ля</td>

        <td colspan="2">Сведения о посреднике (коми&shy;сси&shy;онере, агенте)</td>

        <td rowspan="2">Тип ЛС</td>
        <td rowspan="2">Тип до&shy;го&shy;во&shy;ра</td>
        <td rowspan="2">Статус</td>

        <td rowspan="2" class="s">Номер и дата до&shy;ку&shy;мен&shy;та, подтве&shy;ржда&shy;ющего оплату</td>
        <td rowspan="2" class="s">На&shy;име&shy;но&shy;ва&shy;ние и код валюты</td>


        <td colspan="2" class="s">
            Стоимость продаж по счету-фактуре, разница стоимости по
            корректировочному счету-фактуре (включая НДС) в валюте
            счета-фактуры
        </td>

        <td colspan="3" class="s">
            Стоимость продаж, облагаемых налогом, по
            счету-фактуре, разница стоимости по корректи-
            ровочному счету-фактуре (без НДС) в рублях и
            копейках по ставке
        </td>

        <td colspan="2" class="s">
            Сумма НДС по счету-фактуре,
            разница стоимости по корректи-
            ровочному счету-фактуре в руб-
            лях и копейках, по ставке
        </td>
        <td rowspan="2" class="s">
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

        <td rowspan="2">Дата внесения</td>
        <td rowspan="2">Дата удаления</td>
        <td rowspan="2">Внесение в книгу продаж</td>
    </tr>

    <td>наиме&shy;нова&shy;ние по&shy;сре&shy;дни&shy;ка</td>
    <td>ИНН/КПП по&shy;сре&shy;дни&shy;ка</td>

    <td>в валюте счета- фактуры</td>
    <td>в рублях и копейках</td>

    <td>18 про&shy;цен&shy;тов</td>
    <td>10 про&shy;цен&shy;тов</td>
    <td>0 про&shy;цен&shy;тов</td>

    <td>18 про&shy;цен&shy;тов</td>
    <td>10 про&shy;цен&shy;тов</td>

    <tr>
    </tr>
    </thead>
    <tbody>
    <?php

    use app\helpers\DateTimeZoneHelper;
    use app\models\ClientContragent;
    use app\models\Invoice;

    $query = $filter->search();

    $idx = 1;

    $total = ['sumAll' => 0, 'sum18' => 0, 'sum10' => 0, 'sum0' => 0, 'tax18' => 0, 'tax10' => 0, 'tax' => 0,];

    if ($query)
        foreach ($query->each() as $invoice) : ?>
            <?php /** @var \app\models\filter\SaleBookFilter $invoice */

            if (!$filter->check($invoice)) {
                continue;
            }

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
                $sum_without_tax = $invoice->sum_without_tax;
                $sum_tax = $invoice->sum_tax;

            } catch (Exception $e) {
                Yii::$app->session->addFlash('error', $e->getMessage());
                continue;
            }

            $total['sumAll'] += $sum;
            $total['sum' . $taxRate] += $sum_without_tax;
            $total['tax' . $taxRate] += $sum_tax;

            ?>
            <tr class="<?= ($idx % 2 == 0 ? 'odd' : 'even') ?>">
                <td><?= ($idx++) ?> </td>
                <td><?= ($invoice->type_id == Invoice::TYPE_PREPAID ? '02' : '01') ?></td>
                <td><?= ($invoice->number . '; ' . $invoice->getDateImmutable()->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED)) ?></td>
                <td><?= ($invoice->correction_idx ? $invoice->correction_idx . ' от ' .$invoice->getDateImmutable()->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) : '---')?></td>
                <td>---</td>
                <td>---</td>
                <td><?= trim($contragent->name_full) ?></td>
                <td><?= trim($contragent->inn) ?>
                    <?= ($contragent->legal_type == ClientContragent::LEGAL_TYPE ? '/' . (trim($contragent->kpp) ?: '') : '') ?></td>
                <td>---</td>
                <td>---</td>
                <td><?= $contragent->legal_type ?></td>
                <td><?= $account->contract->business->name ?></td>
                <td><?= $contract->businessProcessStatus->name ?></td>
                <td><?= $invoice->getPaymentsStr() ?: '&nbsp;' ?></td>
                <td><?= $account->currencyModel->name ?> <?= $account->currencyModel->code ?></td>
                <td><?= $printSum($sum) ?></td>
                <td><?= $printSum($sum) ?></td>
                <td><?= $taxRate == 18 ? $printSum($sum_without_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 10 ? $printSum($sum_without_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 0 ? $printSum($sum_without_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 18 ? $printSum($sum_tax) : '&nbsp;' ?></td>
                <td><?= $taxRate == 10 ? $printSum($sum_tax) : '&nbsp;' ?></td>
                <td>&nbsp;</td>
                <td><?= (new DateTime($invoice->add_date))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) ?></td>
                <td><?= $invoice->is_reversal && $invoice->reversal_date ? (new DateTime($invoice->reversal_date))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) : '&nbsp;' ?></td>
                <td></td>
            </tr>
            <?php
        endforeach;
    ?>
    <tr class="even">
        <td colspan="15" align="right">Всего:</td>
        <td><?= $printSum($total['sumAll']) ?></td>
        <td><?= $printSum($total['sumAll']) ?></td>
        <td><?= $printSum($total['sum18']) ?></td>
        <td><?= $printSum($total['sum10']) ?></td>
        <td><?= $printSum($total['sum0']) ?></td>
        <td><?= $printSum($total['tax18']) ?></td>
        <td><?= $printSum($total['tax10']) ?></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </tbody>
</table>
