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
    <li><a href="/?module=newaccounts&action=ext_bills_ifns">Книга покупок (ИФНС)</a></li>
</ul>

<h2>Книга покупок ИФНС</h2>

<form style='display:inline' action='?' id="f_send">
    <input type=hidden name=module value=newaccounts>
    <input type=hidden name=action value=ext_bills_ifns>
    <table>
        <tr>
            <td>С:<br><input type=text1 id="date_from" name=date_from value='{$date_from}' class=text
                             style="width:100px;">
            </td>
            <td>&nbsp;&nbsp;По:<br>&nbsp;&nbsp;
                <input type=text1 id="date_to" name=date_to value='{$date_to}' class=text style="width:100px;">
            </td>
            <td>&nbsp;&nbsp;Фильтровать по:</td>
            <td>
                <select name="filter" id="filter">
                    <option  value="dateRegistrationSf" {if $filter == 'dateRegistrationSf'} selected{/if}>Дате регистрации с/ф</option>
                    <option  value="dateOutSf" {if $filter == 'dateOutSf'} selected{/if}>Дате внешней с/ф</option>
                    <option  value="dateWithoutSf" {if $filter == 'dateWithoutSf'} selected{/if}>С/ф без даты регистрации</option>
                </select>
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
        <td rowspan="2" align="center">&#8470;<br>п/п</td>
        <td rowspan="2" align="center">Код вида операции</td>
        <td rowspan="2" align="center">Номер и дата счета-фактуры продавца</td>
        <td rowspan="2" align="center">Номер и дата исправления счета-фактуры продавца</td>
        <td rowspan="2" align="center">Номер и дата корректировочного счета-фактуры продавца</td>
        <td rowspan="2" align="center">Номер и дата исправления корректировочного счета-фактуры продавца</td>
        <td rowspan="2" align="center">Номер и дата документа, подтверждающего уплату налога</td>
        <td rowspan="2" align="center">Дата принятия на учет  товаров (работ, услуг), имущественных прав</td>
        <td rowspan="2" align="center">Наименование продавца</td>
        <td rowspan="2" align="center">ИНН/КПП продавца</td>
        <td colspan="2" align="center">
            Сведения о посреднике
            (комиссионере, агенте, экспедиторе,
            лице, выполняющем функции застройщика)
        </td>
        <td rowspan="2" align="center">Регистрационный номер таможенной декларации</td>

        <td rowspan="2" align="center">Наименование и код валюты</td>
        <td rowspan="2" align="center">
            Стоимость покупок по счету-фактуре,
            разница стоимости по корректировочному
            счету-фактуре (включая НДС)
            в валюте счета-фактуры
        </td>
        <td rowspan="2" align="center">
            Сумма НДС по счету-фактуре,
            разница суммы НДС по
            корректировочному счету-фактуре,
            принимаемая к вычету
            в рублях и копейках
        </td>
        <td rowspan="2" align="center">Дата регистрации с/ф</td>
    </tr>
    <tr>
        <td align="center">
            наименование посредника
        </td>
        <td align="center">
            ИНН/КПП посредника
        </td>
    </tr>

    </thead>
    <tbody>
    {foreach from=$data item=item name=outer}
        <tr>
            <td>{$smarty.foreach.outer.iteration}</td>
            <td>01</td>
            <td>{$item.bill_no} от {$item.ext_invoice_date}</td>
            <td>{if $item.correction_number} {$item.correction_number} от </br> {$item.correction_date} {/if}</td>
            <td></td>
            <td></td>
            <td></td>
            <td>{$item.ext_invoice_date}</td>
            <td>{$item.name_full}</td>
            <td>{if $item.legal_type != 'person'}{$item.inn}/ {$item.kpp}{/if}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right">{$item.sum|replace:".":","}</td>
            <td class="text-right">{$item.vat|replace:".":","}</td>
            <td>{$item.ext_registration_date}</td>
        </tr>

    {/foreach}
    {foreach from=$totals key=currency item=total}
        <tr>
            <td colspan="14" style="text-align: right">Итого:</td>
            <td>{$total.sum|replace:".":","}</td>
            <td>{$total.vat|replace:".":","}</td>
            <td>&nbsp;</td>
        </tr>
    {/foreach}
    </tbody>
</table>

<script>
  optools.DatePickerInit();
</script>

