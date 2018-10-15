{literal}
    <STYLE>
        .price {
            font-size:15px;
        }
        body {
            color: black;
            font-size: 8pt;
        }
        td {
            color: black;
        }
        thead tr td {
            font-weight:bold;
        }
        h2 {
            text-align:center;
            font-size: 12pt;
        }
        h3 {
            text-align:center;
        }
        p {font-size: 8pt;}
        td {font-family: Verdana; font-size: 8pt;}
        th {font-family: Verdana; font-size: 6pt;}
        small {font-size: 6.5pt;}
        strong {font-size: 6.5pt;}
    </STYLE>
{/literal}
<ul class="breadcrumb"><li><a href="/">Главная</a></li>
    <li class="active">Бухгалтерия</li>
    <li><a href="/?module=newaccounts&action=ext_bills">Внешние счета</a></li>
</ul>

<h2>Внешние счета</h2>

<form style='display:inline' action='?' id="f_send">
    <input type=hidden name=module value=newaccounts>
    <input type=hidden name=action value=ext_bills>
    <table>
        <tr>
            <td>С:<br><input type=text id="date_from" name=date_from value='{$date_from}' class=text style="width:100px;"></td>
            <td>По:<br><input type=text id="date_to" name=date_to value='{$date_to}' class=text style="width:100px;"></td>
            <td><br><input type=submit value='Поехали' class=button></td>
        </tr>
    </table>

</form>
<br>
<br>

    <TABLE class=price cellSpacing=0 cellPadding=2 border=1>
        <thead>
            <tr>
                <td>&#8470; счета</td>
                <td>Дата счета</td>
                <td>ЛС</td>
                <td>Внешний &#8470; счета</td>
                <td>Номер договора</td>
                <td>Контрагент</td>
                <td>ИНН</td>
                <td>КПП</td>
                <td>Юр. адресс</td>
                <td>Сумма</td>
                <td>Валюта</td>
                <td>Организация</td>
            </tr>
        </thead>
        <tbody>
            {foreach from=$data item=item name=outer}
                <tr>
                    <td>{$item.bill_no}</td>
                    <td>{$item.bill_date}</td>
                    <td>{$item.id}</td>
                    <td>{$item.number}</td>
                    <td>{$item.ext_bill_no}</td>
                    <td>{$item.name_full}</td>
                    <td>{$item.inn}</td>
                    <td>{$item.kpp}</td>
                    <td>{$item.address_jur}</td>
                    <td class="text-right">{$item.sum|replace:".":","}</td>
                    <td >{$item.currency}</td>
                    <td>{$item.orgznization_name}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>


<script>
  optools.DatePickerInit();
</script>

