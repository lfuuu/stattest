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
    <li><a href="/?module=newaccounts&action=ext_bills">Книга покупок</a></li>
</ul>

<h2>Книга покупок</h2>

<form style='display:inline' action='?' id="f_send">
    <input type=hidden name=module value=newaccounts>
    <input type=hidden name=action value=ext_bills>
    <table>
        <tr>
            <td>С:<br><input type=text1 id="date_from" name=date_from value='{$date_from}' class=text
                             style="width:100px;"></td>
            <td>По:<br><input type=text1 id="date_to" name=date_to value='{$date_to}' class=text style="width:100px;">
            </td>
        </tr>
        <tr>
            <td colspan="2">
                &nbsp;Организация:{html_options options=$organizations selected=$organization_id name="organization_id"}</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;Валюта:{html_options options=$currencies selected=$currency name="currency"}</td>
        </tr>
        <tr>
            <td>&nbsp;Только с внешней с/ф:<input type="checkbox" name='is_ext_invoice_only' value='1'{if $is_ext_invoice_only} checked{/if}> </td>
            <td> в Excel: <input type=checkbox name='is_to_excel' value="1" class=button> </td>
        </tr>
        <tr>
            <td colspan="2"><input type=submit value='Поехали' class=button></td>
        </tr>
    </table>

</form>
<br>
<br>

<TABLE class=price cellSpacing=0 cellPadding=2 border=1>
    <thead>
    <tr>
        <td>п/п</td>
        <td>&#8470; счета</td>
        <td>Дата счета</td>
        <td>ЛС</td>
        <td>Номер договора</td>
        <td>Внешний &#8470; счета</td>
        <td>Дата внешнего счета</td>
        <td>Внешний &#8470; с/ф</td>
        <td>Дата внешней с/ф</td>
        <td>Внешний &#8470; акта</td>
        <td>Дата внешнего акта</td>
        <td>Контрагент</td>
        <td>ИНН</td>
        <td>КПП</td>
        <td>Юр. адресс</td>
        <td>Сумма счета</td>
        <td>Сумма без НДС</td>
        <td>НДС</td>
        <td>Сумма с НДС</td>
        <td>Валюта</td>
    </tr>
    </thead>
    <tbody>
    {foreach from=$data item=item name=outer}
        <tr>
            <td>{$smarty.foreach.outer.iteration}</td>
            <td>{$item.bill_no}</td>
            <td>{$item.bill_date}</td>
            <td>{$item.id}</td>
            <td>{$item.number}</td>
            <td>{$item.ext_bill_no}</td>
            <td>{$item.ext_bill_date}</td>
            <td>{$item.ext_invoice_no}</td>
            <td>{$item.ext_invoice_date}</td>
            <td>{$item.ext_akt_no}</td>
            <td>{$item.ext_akt_date}</td>
            <td>{$item.name_full}</td>
            <td>{$item.inn}</td>
            <td>{$item.kpp}</td>
            <td>{$item.address_jur}</td>
            <td class="text-right">{$item.bill_sum|replace:".":","}</td>
            <td class="text-right">{$item.sum_without_vat|replace:".":","}</td>
            <td class="text-right">{$item.vat|replace:".":","}</td>
            <td class="text-right">{$item.sum|replace:".":","}</td>
            <td>{$item.currency}</td>
        </tr>
    {/foreach}
    {foreach from=$totals key=currency item=total}
        <tr>
            <td colspan="15" style="text-align: right">Итого:</td>
            <td>{$total.bill_sum}</td>
            <td>{$total.sum_without_vat}</td>
            <td>{$total.vat}</td>
            <td>{$total.sum}</td>
            <td>{$currency} ({$total.count})</td>
        </tr>
    {/foreach}
    </tbody>
</table>


<script>
  optools.DatePickerInit();
</script>

