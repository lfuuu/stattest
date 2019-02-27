<?php

/** @var \app\models\filter\SaleBookFilter $filter */

?>
<h2>Sale book</h2>
<table class="price" cellspacing="4" cellpadding="2" border="1"
       style="border-collapse: collapse; font: normal 8pt sans-serif; padding: 2px 2px 2px 2px;">

    <thead>
    <tr>
        <td>Дата счета</td>
        <td>Номер УЛС / ЛС</td>
        <td width="500px">Название клиента</td>
        <td>Тип договора</td>
        <td>INVOICE number</td>
        <td>Срок оплаты счета</td>
        <td>Всего к оплате</td>
        <td>Наименование валюты счета</td>
        <td>Сумма НДС</td>
        <td>netto</td>
        <td>Процент НДС</td>
        <td>Евро ИНН номер</td>
        <td>ИНН страны покупателя</td>
        <td>ссылка на счет в PDF формате</td>
    </tr>
    </thead>
    <tbody>
    <?php

    use app\classes\Html;
    use app\helpers\DateTimeZoneHelper;

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
                $bill = $invoice->bill;
                $account = $bill->clientAccount;
                $contract = $account->contract;
                $contragent = $contract->contragent;

                $taxRate = $account->getTaxRate($bill->bill_date);

                $sum = $invoice->sum;
                $sum_without_tax = $invoice->sum_without_tax;
                $sum_tax = $invoice->sum_tax;

            } catch (Exception $e) {
                Yii::$app->session->addFlash('error', $e->getMessage());
                continue;
            }

            $total['sumAll'] += $sum;
            $total['sum'] += $sum_without_tax;
            $total['tax'] += $sum_tax;

            ?>
            <tr class="<?= ($idx % 2 == 0 ? 'odd' : 'even') ?>">
                <td><?= (new DateTime($bill->bill_date))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) ?> </td>
                <td><?= $account->id ?></td>
                <td ><?= trim($contragent->name_full) ?></td>
                <td nowrap><?= $contract->business->name ?> / <?= $contract->businessProcessStatus->name ?></td>
                <td nowrap><?= $invoice->number ?></td>
                <td><?= (new DateTime($bill->pay_bill_until))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) ?></td>
                <td><?= $printSum($invoice->sum) ?></td>
                <td><?= $invoice->bill->currency ?></td>
                <td><?= $printSum($invoice->sum_tax) ?></td>
                <td><?= $printSum($invoice->sum_without_tax) ?></td>
                <td><?= $taxRate ?>%</td>
                <td nowrap><?= $contragent->inn_euro ?></td>
                <td nowrap><?= $contragent->inn ?></td>
                <td nowrap=""><?= Html::a($account->id . '-' . $invoice->number . '.pdf', [
                        '/',
                        'module' => 'newaccounts',
                        'action' => 'bill_mprint',
                        'bill' => $invoice->bill_no,
                        'invoice2' => $invoice->type_id,
                        'invoice_id' => $invoice->id
                    ], ['target' => '_blank']) ?></td>
            </tr>
            <?php
        endforeach;
    ?>
    <tr class="even">
        <td colspan="6" align="right">Всего:</td>
        <td><?= $printSum($total['sumAll']) ?></td>
        <td>&nbsp;</td>
        <td><?= $printSum($total['tax']) ?></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    </tbody>
</table>
