<?php

/** @var \app\models\filter\SaleBookFilter $filter */

?>
<h2>Sale book</h2>
<table class="price" cellspacing="4" cellpadding="2" border="1"
       style="border-collapse: collapse; font: normal 8pt sans-serif; padding: 2px 2px 2px 2px;">

    <thead>
    <tr>
        <td>Registry ID</td>
        <td>Invoice Date</td>
        <td>Account ID</td>
        <td>Country</td>
        <td>Type</td>
        <td width="500px">Client name</td>
        <td width="500px">Address</td>
        <td>Agreement type</td>
        <td>INVOICE number (RYYmmOO-NNNN)</td>
        <td>If storno, Original INVOICE number</td>
        <td>Due date</td>
        <td>Total</td>

        <td>Currency</td>
        <td>VAT total</td>
        <td>Net</td>
        <td>VAT %</td>

        <td>Exchange rate (Euro)</td>
        <td>Net (Euro)</td>
        <td>VAT (Euro)</td>
        <td>Total (Euro)</td>

        <td>EU VAT №</td>
        <td>Local VAT №</td>
        <td>Link to invoice (internal)</td>
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

//            if (!$filter->check($invoice)) {
//                continue;
//            }

            if (!$invoice->bill || !$invoice->bill->clientAccount) {
                Yii::$app->session->addFlash('error', 'С/ф без счета: ' . $invoice->number);
                continue;
            }
            try {
                $bill = $invoice->bill;
                $account = $bill->clientAccount;
                $contract = $account->clientContractModel;
                $contragent = $contract->contragent;

                $taxRate = $account->getTaxRate($bill->bill_date);

                $sum = $invoice->sum;
                $sum_without_tax = $invoice->sum_without_tax;
                $sum_tax = $invoice->sum_tax;

                $rate = $invoice->getCurrencyRateToEuro();

                $inEuro = [
                    'rate' => $rate,
                    'total' => $invoice->sum * $rate,
                    'vat' => $invoice->sum_tax * $rate,
                    'net' => $invoice->sum_without_tax * $rate,
                ];


            } catch (Exception $e) {
                Yii::$app->session->addFlash('error', $e->getMessage());
                continue;
            }

            $total['sumAll'] += $sum;
            $total['sum'] += $sum_without_tax;
            $total['tax'] += $sum_tax;

            ?>
            <tr class="<?= ($idx % 2 == 0 ? 'odd' : 'even') ?>">
                <td><a href="./?module=newaccounts&action=bill_view&bill=<?= $invoice->bill_no ?>"
                       target="_blank"><?= $invoice->bill_no ?></a></td>
                <td><?= (new DateTime($invoice->date))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) ?> </td>
                <td><?= $account->id ?></td>
                <td><?= $contragent->country->name ?></td>
                <td><?= $contragent->legal_type ?></td>
                <td><?= trim($contragent->name_full) ?></td>
                <td><?= trim($contragent->legal_type == \app\models\ClientContragent::PERSON_TYPE ? $contragent->person->registration_address : $contragent->address_jur) ?></td>
                <td nowrap><?= $contract->business->name ?> / <?= $contract->businessProcessStatus->name ?></td>
                <td nowrap><?= $invoice->number ?></td>
                <td nowrap><?= $invoice->is_reversal && $invoice->getReversalInvoice() ? $invoice->getReversalInvoice()->number : '' ?></td>
                <td><?= (new DateTime($bill->pay_bill_until))->format(DateTimeZoneHelper::DATE_FORMAT_EUROPE_DOTTED) ?></td>
                <td><?= $printSum($invoice->sum) ?></td>
                <td><?= $invoice->bill->currency ?></td>
                <td><?= $printSum($invoice->sum_tax) ?></td>
                <td><?= $printSum($invoice->sum_without_tax) ?></td>
                <td><?= $taxRate ?>%</td>

                <td><?= $printSum($inEuro['rate'], 6) ?> </td>
                <td><?= $printSum($inEuro['net']) ?> </td>
                <td><?= $printSum($inEuro['vat']) ?> </td>
                <td><?= $printSum($inEuro['total']) ?> </td>

                <td nowrap><?= $contragent->inn_euro ?></td>
                <td nowrap><?= $contragent->inn ?></td>
                <td nowrap=""><?= Html::a($account->id . '-' . $invoice->number . '.pdf', [
                        '/',
                        'module' => 'newaccounts',
                        'action' => 'bill_mprint',
                        'bill' => $invoice->bill_no,
                        'invoice2' => $invoice->type_id,
                        'invoice_id' => $invoice->id,
                        'isDirectLink' => 1,
                    ], ['target' => '_blank']) ?></td>
            </tr>
            <?php
        endforeach;
    ?>
    <!--
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
    -->
    </tbody>
</table>

<br>
<br>
<br>
<div class="well" style="width: 320px;">
    <button id="downloadAll" type="button" class="btn btn-warning">Скачать все закрывающие документы</button>
    <script>
        $(document).ready(function () {
            $('#downloadAll').click(function (e) {
                e.preventDefault();
                var ll = $('a[href*=invoice_id]').toArray();

                downloadAll(ll);
                async function downloadAll(elements) {
                    var count = 0;
                    for (var e in elements) {
                        download(elements[e]);
                        if (++count >= 5) {
                            await pause(1000);
                            count = 0;
                        }
                    }
                    alert("Загружка завершена!");
                }

                function download(url) {
                    var a = document.createElement("a");
                    a.setAttribute('href', url);
                    a.setAttribute('download', '');
                    a.click();
                }

                function pause(msec) {
                    return new Promise(
                        (resolve, reject) => {
                            setTimeout(resolve, msec || 1000);
                        }
                    );
                }
            });
        });
    </script>

</div>

