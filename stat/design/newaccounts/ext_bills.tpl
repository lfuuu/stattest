{literal}
    <STYLE>
        .price {
            font-size: 15px;
        }

        body {
            color: black;
            font-size: 8pt;
        }

        td {
            color: black;
        }

        thead tr td {
            font-weight: bold;
        }

        h2 {
            text-align: center;
            font-size: 12pt;
        }

        h3 {
            text-align: center;
        }

        p {
            font-size: 8pt;
        }

        td {
            font-family: Verdana;
            font-size: 8pt;
        }

        th {
            font-family: Verdana;
            font-size: 6pt;
        }

        small {
            font-size: 6.5pt;
        }

        strong {
            font-size: 6.5pt;
        }
    </STYLE>
{/literal}
<ul class="breadcrumb">
    <li><a href="/">Главная</a></li>
    <li class="active">Бухгалтерия</li>
    <li><a href="/?module=newaccounts&action=ext_bills">Европейская книга покупок</a></li>
</ul>

<h2>Европейская книга покупок</h2>

<form style='display:inline' action='?' id="f_send">
    <input type=hidden name=module value=newaccounts>
    <input type=hidden name=action value=ext_bills>
    <table>
        <tr>
            <td>С:<br><input type=text1 id="date_from" name=date_from value='{$date_from}' class="form-control"
                             style="width:100px;"></td>
            <td>По:<br><input type=text1 id="date_to" name=date_to value='{$date_to}' class="form-control"
                              style="width:100px;">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                &nbsp;Организация:{html_options options=$organizations selected=$organization_id name="organization_id"}</td>
        </tr>
        <tr>
            <td colspan="2">
                {html_options options=$period_type_list selected=$period_type name="period_type"}</td>
        </tr>
        <tr>
            <td>&nbsp;Только с внешней с/ф:<input type="checkbox" name='is_ext_invoice_only'
                                                  value='1'{if $is_ext_invoice_only} checked{/if}></td>
            <td> в Excel: <input type=checkbox name='is_to_excel' value="1" class=button></td>
        </tr>
        <tr>
            <td>BMD:<input type="checkbox" name='is_bmd' value='1'{if $is_bmd} checked{/if}></td>
            <td>{if $is_bmd} в Excel: <input type=checkbox name='is_to_excel_bmd' value="1" class=button>{/if}</td>
        </tr>
        <tr>
            <td colspan="2"><input type=submit value='Поехали' class="btn btn-primary"></td>
        </tr>
    </table>

</form>
<br>
<br>
{if $is_bmd}
    <TABLE class=price cellSpacing=0 cellPadding=2 border=1>
        <thead>
        <tr>
            <td>belegnr</td>
            <td>satzart</td>
            <td>buchsymbol</td>
            <td>buchcode</td>
            <td>gegenbuchkz</td>
            <td>verbuchkz</td>
            <td>konto</td>
            <td>buchdatum</td>
            <td>belegdatum</td>
            <td>extbelegnr</td>
            <td>waehrung</td>
            <td>fwbetrag</td>
            <td>betrag</td>
            <td>fwsteuer</td>
            <td>steuer</td>
            <td>prozent</td>
            <td>steuercode</td>
            <td>gkonto</td>
            <td>dokument</td>

            <td>Ссылка на оригинальную с/ф поставщика</td>
        </tr>
        </thead>
        <tbody>
        {foreach from=$data item=item name=outer}
            <tr>
                <td>{$smarty.foreach.outer.iteration}</td>
                <td>0</td>
                <td>ER</td>
                <td>2</td>
                <td>E</td>
                <td>A</td>
                <td><a href="/client/view?id={$item.account_id}" target="_blank">{$item.account_id}</a></td>
                <td>{$item.invoice_date}</td>
                <td>{$item.due_date}</td>
                <td><a href="/?module=newaccounts&action=bill_view&bill={$item.bill_no}" target="_blank">{$item.ext_invoice_no}</a></td>
                <td>{$item.currency}</td>
                <td>{$item.bmd.fwbetrag}</td>
                <td>{$item.bmd.betrag}</td>
                <td>{$item.bmd.fwsteuer}</td>
                <td>{$item.bmd.steuer}</td>
                <td>{$item.bmd.prozent}</td>
                <td>{$item.bmd.steuercode}</td>
                <td>{$item.bmd.gkonto}</td>
                <td>See STAT bill <a href="/?module=newaccounts&action=bill_view&bill={$item.bill_no}" target="_blank">{$item.bill_no}</a> for details</td>
                <!-- td>\\tsclient\C\ER22INVOICE\Q3\RA0013112022.pdf</td -->
                <td>{if $item.file_name}<a
                        href="/?module=newaccounts&action=bill_ext_file_get&bill_no={$item.bill_no|escape:'url'}"
                        class="">{$item.file_name}</a>{else}...{/if}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{else}
<TABLE class=price cellSpacing=0 cellPadding=2 border=1>
    <thead>
    <tr>
        <td>п/п</td>
        <td>Проводка</td>
        <td>месяц регистрации с/ф</td>
        <td>УЛС</td>
        <td>Контрагент</td>
        <td>Страна контрагента</td>
        <td>ИНН</td>
        <td>ЕВРО ИНН</td>
        <td>Внешний &#8470; с/ф</td>
        <td>Дата внешней с/ф</td>
        <td>Срок оплаты внешней с/ф</td>
        <td>Сумма без НДС из с/ф поставщика</td>
        <td>Сумма НДС из с/ф поставщика</td>
        <td>Суммв с НДС из с/ф поставщика</td>
        <td>Валюта</td>
        <td>Курс (Евро)</td>

        <td>Сумма без НДС из с/ф поставщика (ЕВРО)</td>
        <td>Сумма НДС из с/ф поставщика (ЕВРО)</td>
        <td>Суммв с НДС из с/ф поставщика (ЕВРО)</td>

        <td>Ссылка на оригинальную с/ф поставщика</td>
    </tr>
    </thead>
    <tbody>
    {foreach from=$data item=item name=outer}
        <tr>
            <td>{$smarty.foreach.outer.iteration}</td>
            <td>
                <a href="/?module=newaccounts&action=bill_view&bill={$item.bill_no}" target="_blank">{$item.bill_no}</a>
            </td>
            <td>{$item.registration_date|date_format:'%m'}</td>
            <td><a href="/client/view?id={$item.account_id}" target="_blank">{$item.account_id}</a></td>
            <td>{$item.name_full}</td>
            <td>{$item.country_name}</td>
            <td>{$item.inn}</td>
            <td>{$item.inn_euro}</td>
            <td>{$item.ext_invoice_no}</td>
            <td>{$item.invoice_date}</td>
            <td>{$item.due_date}</td>
            <td>{$item.sum_without_vat|string_format:"%4.2f"}</td>
            <td>{$item.vat|string_format:"%4.2f"}</td>
            <td>{$item.sum|string_format:"%4.2f"}</td>
            <td>{$item.currency}</td>
            <td>{$item.rate}</td>
            <td>{$item.sum_without_vat_euro|string_format:"%4.2f"}</td>
            <td>{$item.vat_euro|string_format:"%4.2f"}</td>
            <td>{$item.sum_euro|string_format:"%4.2f"}</td>
            <td>{if $item.file_name}<a
                    href="/?module=newaccounts&action=bill_ext_file_get&bill_no={$item.bill_no|escape:'url'}"
                    class="">{$item.file_name}</a>{else}...{/if}</td>
        </tr>
    {/foreach}
    {foreach from=$totals key=currency item=total}
        <tr>
            <td colspan="11" style="text-align: right">Итого:</td>
            <td>{$total.sum_without_vat}</td>
            <td>{$total.vat}</td>
            <td>{$total.sum}</td>
            <td>{$currency}&nbsp;({$total.count})</td>
            <td>&nbsp;</td>
            {if $currency == 'EUR'}
                <td>{$totalEuro.sum_without_vat}</td>
                <td>{$totalEuro.vat}</td>
                <td>{$totalEuro.sum}</td>
            {else}
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            {/if}
            <td>&nbsp;</td>
        </tr>
    {/foreach}
    </tbody>
</table>
{/if}
<script>
    {literal}
    //optools.DatePickerInit();
    $(function () {
        $('#date_from').datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'mm-yy',
            onClose: function (dateText, inst) {
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
        });
    });

    $(function () {
        $('#date_to').datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'mm-yy',
            onClose: function (dateText, inst) {
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
        });
    });
    {/literal}
</script>

<style>
    {literal}
    .ui-datepicker-calendar {
        display: none;
    }

    {/literal}
</style>
<BR>
<BR>
<BR>
<BR>
<div class="well" style="width: 225px;">
    <button id="downloadAll" class="btn btn-warning">Скачать все документы</button>
</div>

<script>
    {literal}
    $(document).ready(function () {
        $('#downloadAll').click(function (e) {
            e.stopPropagation();

            $('a[href*=bill_ext_file_get]').each(function(b, a) {
                window.open(a.href);
            });
/*
            var ll = $('a[href*=bill_ext_file_get]');

            var link = document.createElement('a');

            link.setAttribute('download', null);
            link.style.display = 'none';

            document.body.appendChild(link);

            for (var i = 0; i < ll.length; i++) {
                link.setAttribute('href', ll[i]);
                link.click();
            }

            document.body.removeChild(link);
*/
        });
    });
    {/literal}
</script>
