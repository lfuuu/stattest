{if $fullscreen}
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title>Книга продаж</title>
    <meta http-equiv=Content-Type content="text/html; charset=utf-8"/>
    {literal}
        <style type="text/css">
            .price {
                font-size: 14px;
            }

            thead tr td {
                font-weight: bold;
            }

            thead tr td.s {
                padding: 1px 1px 1px 1px;
                font-size: 12px;
            }

            h2 {
                text-align: center;
            }

            h3 {
                text-align: center;
            }
        </style>
    {/literal}
</head>

{if strtotime($date_from) >= strtotime("2017-10-01")}
    {assign var="isChanges20171001" value=1}
{else}
    {assign var="isChanges20171001" value=0}
{/if}

<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF">
{if $isChanges20171001}
    <div class="w100" style="text-align: right">
        Приложение № 5<br/>
        к постановлению Правительства<br/>
        Российской Федерации<br/>
        от 26 декабря 2011 г. № 1137<br/>
        (в ред. Постановления Правительства РФ от 19.08.2017 № 981)<br/>
    </div>
{/if}
<h2>КНИГА ПРОДАЖ</h2>
Продавец __________________________________________________________________________________________________________<br/>
Идентификационный номер и код причины постановки на учет налогоплательщика-продавца
__________________________________________________________________________________________________________<br>
Продажа за период с {$date_from_val|mdate:"d месяца Y г."} по {$date_to_val|mdate:"d месяца Y г."}<br/>

<table class="price" cellSpacing="0" cellPadding="2" border="1">
    {else}
    <form style="display:inline" action="" ?
    ">
    <input type="hidden" name="module" value="newaccounts"/>
    <input type="hidden" name="action" value="balance_sell"/>
    От: <input id="date_from" type="text" name="date_from" value="{$date_from}" class="text"/>
    До: <input id="date_to" type="text" name="date_to" value="{$date_to}" class="text"/><br/>
    Компания:
    <select class="text" name="organization_id">
        {html_options options=$organizations selected=$organization_id}
    </select>
    Полный экран: <input type="checkbox" name="fullscreen" value="1"/>&nbsp;
    в Excel: <input type="checkbox" name="excel" value="1"/><br/>
    <input type="submit" value="Показать" class="button" name="do"/>
    </form>
    <h2>Книга продаж</h2>
    <table class="price" cellspacing="4" cellpadding="2" border="1"
           style="border-collapse: collapse; font: normal 8pt sans-serif; padding: 2px 2px 2px 2px;">
        {/if}

        <thead>
        <tr>
            <td width="5%" rowspan="4" class="s">№<br/>п/п</td>
            <td width="5%" rowspan="4" class="s">Код<br/>вида<br/>опера-<br/>ции</td>
            <td width="10%" rowspan="4" class="s">Дата и номер счета-фактуры продавца</td>
            {if $isChanges20171001}
                <td width="*" rowspan="4">Регистра-<br/>ционный<br/> номер<br/> тамо-<br/>женной<br/> декла-<br/> рации
                </td>
                <td width="*" rowspan="4">Код<br/> вида<br/> товара</td>
            {/if}
            <td width="*" rowspan="4">Наименование покупателя</td>
            <td width="5%" rowspan="4">ИНН/КПП<br/>покупателя</td>
            <td width="5%" rowspan="4">Тип ЛС</td>
            <td width="5%" rowspan="4">Тип договора</td>
            <td width="5%" rowspan="4">Статус</td>
            <td width="5%" rowspan="4" class="s">Номер и дата документа, подтверждающего оплату</td>
            <td width="5%" rowspan="4">Всего продаж, включая НДС</td>
            <td width="30%" colspan="8">В том числе</td>
        </tr>
        <tr>
            <td width="53%" colspan="7">продажи, облагаемые налогом по ставке</td>
            <td width="5%" rowspan="3" class="s">продажи, освобождаемые от налога</td>
        </tr>
        <tr>
            <td colspan="2" class="s">18 процентов (5)</td>
            <td colspan="2" class="s">10 процентов (6)</td>
            <td rowspan="2" class="s">0 процентов</td>
            <td colspan="2" class="s">20 процентов* (8)</td>
        </tr>
        <tr>
            <td class="s">стоимость продаж<br>без НДС</td>
            <td class="s">сумма НДС</td>
            <td class="s">стоимость продаж<br>без НДС</td>
            <td class="s">сумма НДС</td>
            <td class="s">стоимость продаж<br>без НДС</td>
            <td class="s">сумма НДС</td>
        </tr>
        </thead>
        <tbody>
        {foreach from=$data item=r name=outer}
            {assign var="index" value=$smarty.foreach.outer.index+1}
            <tr class="{cycle values="even,odd"}">
                <td>{$index}</td>
                <td>01</td>
                <td>
                    <nobr>{$r.inv_no};</nobr>
                    <nobr>{$r.inv_date|mdate:"d.m.Y"}</nobr>
                </td>
                {if $isChanges20171001}
                    <td class="s">{$r.gtd}</td>
                    <td class="s">-</td>
                {/if}
                <td class="s">{$r.company_full}&nbsp;</td>
                <td>{if $r.inn}{$r.inn}{if $r.type == 'org'}/{if $r.kpp}{$r.kpp}{/if}{/if}{else}&nbsp;{/if}</td>
                <td>{$r.type}</td>
                <td>{$r.contract}</td>
                <td>{$r.contract_status}</td>
                <td>{if isset($r.payments) && $r.payments}{$r.payments}{else}&nbsp;{/if}</td>
                <td>{$r.sum|round:2|replace:".":","}</td>
                <td>{$r.sum_without_tax|round:2|replace:".":","}</td>
                <td>{$r.sum_tax|round:2|replace:".":","}</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
                <td>0</td>
            </tr>
        {/foreach}
        <tr class="{cycle values="even,odd"}">
            <td colspan="9" align="right">Всего:</td>
            <td>{$sum.sum|round:2|replace:".":","}</td>
            <td>{$sum.sum_without_tax|round:2}</td>
            <td>{$sum.sum_tax|round:2}</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
            <td>0</td>
        </tr>
        </tbody>
    </table>

    <script type="text/javascript">
      optools.DatePickerInit();
    </script>

    {if $correctionList}
        {foreach from=$correctionList item=correctionDayList key=listDate}
            {assign var="listSum" value=0}
            <hr>
            <h2>Дополнительный лист составлен: {$listDate|mdate:'d.m.Y'}</h2>
            <table class="price" cellspacing="4" cellpadding="2" border="1"
                   style="border-collapse: collapse; font: normal 8pt sans-serif; padding: 2px 2px 2px 2px;">
                <thead>
                <tr>
                    <td width="4%" rowspan="2" class="s">№<br/>п/п</td>
                    <td width="4%" rowspan="2" class="s">Код вида опе&shy;ра&shy;ции</td>
                    <td width="4%" rowspan="2" class="s">Номер и дата счета-факту&shy;ры про&shy;дав&shy;ца</td>
                    <td width="4%" rowspan="2">Регистра&shy;ционный номер тамо&shy;женной декла&shy;рации</td>
                    <td width="4%" rowspan="2">Код вида товара</td>
                    <td width="4%" rowspan="2">Номер и дата исправ&shy;ления счета-факту&shy;ры продав&shy;ца</td>
                    <td width="4%" rowspan="2">Номер и дата коррек&shy;тировоч&shy;ного счета-факту&shy;ры продав&shy;ца
                    </td>
                    <td width="4%" rowspan="2">Номер и дата исправ&shy;ления коррек&shy;тиро&shy;вочного счета-факту&shy;ры
                        продав&shy;ца
                    </td>
                    <td width="4%" rowspan="2">Наиме&shy;нование покупа&shy;теля</td>
                    <td width="4%" rowspan="2">ИНН/КПП покупа&shy;теля</td>
                    <td width="*" colspan="2">Сведения о посреднике (комиссионере, агенте)</td>
                    <td width="4%" rowspan="2">Ном&shy;ер и дата доку&shy;мента, подтвер&shy;ждаю&shy;щего оплату</td>
                    <td width="4%" rowspan="2">Наиме&shy;нование и код валюты</td>
                    <td width="*" colspan="2" class="s">Стоимость продаж по счету-фактуре, разница стоимости по
                        корректиро&shy;вочному
                        счету-фактуре
                        (включая НДС) в валюте счета- фактуры
                    </td>
                    <td width="*" colspan="3">Стоимость продаж, облагаемых налогом, по счету- фактуре, разница стоимости
                        по
                        корректировочному счету-фактуре
                        (без НДС) в рублях и копейках, по ставке
                    </td>
                    <td width="*" colspan="2">Сумма НДС по счету-фактуре, разница суммы налога по корректи&shy;ровочному
                        счету-фактуре
                        в рублях и копейках, по ставке
                    </td>
                    <td width="5%" rowspan="2">Стоимость продаж, освобож&shy;даемых от налога, по счету-фактуре, разница
                        стоимости по корректиро&shy;вочному
                        счету-фактуре в рублях и копейках
                    </td>
                </tr>
                <tr>
                    <td width="4%">наимено&shy;вание посред&shy;ника</td>
                    <td width="4%" class="s">ИНН/КПП посред&shy;ника</td>
                    <td width="4%" class="s">в валюте счета-фак&shy;туры</td>
                    <td width="4%" class="s">в рублях и ко&shy;пей&shy;ках</td>
                    <td width="4%" class="s">18 про&shy;цен&shy;тов</td>
                    <td width="4%" class="s">10 про&shy;цен&shy;тов</td>
                    <td width="4%" class="s">0 про&shy;цен&shy;тов</td>
                    <td width="4%" class="s">18 про&shy;цен&shy;тов</td>
                    <td width="4%" class="s">10 про&shy;цен&shy;тов</td>
                </tr>
                </thead>
                <tr>
                    <td>1</td>
                    <td>2</td>
                    <td>3</td>
                    <td>3а</td>
                    <td>3б</td>
                    <td>4</td>
                    <td>5</td>
                    <td>6</td>
                    <td>7</td>
                    <td>8</td>
                    <td>9</td>
                    <td>10</td>
                    <td>11</td>
                    <td>12</td>
                    <td>13а</td>
                    <td>13б</td>
                    <td>14</td>
                    <td>15</td>
                    <td>16</td>
                    <td>17</td>
                    <td>18</td>
                    <td>19</td>
                </tr>
                <tr>
                    <td colspan="16" style="text-align: right; font-weight: bold;">Итого</td>
                    <td>0,00</td>
                    <td></td>
                    <td></td>
                    <td>0,00</td>
                    <td></td>
                    <td></td>
                </tr>
                {assign var="idx" value=0}
                {assign var="listSumVat" value=0}
                {assign var="listSumWithoutVat" value=0}

                {foreach from=$correctionDayList item=row}
                    {assign var="rowSum" value=`$row.originalSum*-1`}
                    {math assign="rowSumVat" equation="round($rowSum/1.18,2)"}
                    {assign var="rowSumWithoutVat" value=`$rowSum-$rowSumVat`}

                    {assign var="listSumVat" value=`$listSumVat+$rowSumVat`}
                    {assign var="listSumWithoutVat" value=`$listSumWithoutVat+$rowSumWithoutVat`}

                    {assign var="idx" value=`$idx+1`}
                    <tr>
                        <td>{$idx}</td>
                        <td>01</td>
                        <td>{$row.bill_no}-{$row.type_id} от {$row.invoiceDate|mdate:'d.m.Y'}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>{$row.clientAccount.company_full}</td>
                        <td>{$row.clientAccount.inn}/{$row.clientAccount.kpp}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>{$rowSum|round:2|replace:".":","}</td>
                        <td>{$rowSumVat|round:2|replace:".":","}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>{$rowSumWithoutVat|round:2|replace:".":","}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    {assign var="rowSum" value=`$row.sum`}
                    {math assign="rowSumVat" equation="round($rowSum/1.18,2)"}
                    {assign var="rowSumWithoutVat" value=`$row.sum-$rowSumVat`}

                    {assign var="listSumVat" value=`$listSumVat+$rowSumVat`}
                    {assign var="listSumWithoutVat" value=`$listSumWithoutVat+$rowSumWithoutVat`}

                    {assign var="idx" value=`$idx+1`}
                    <tr>
                        <td>{$idx}</td>
                        <td>01</td>
                        <td>{$row.bill_no}-{$row.type_id} от {$row.invoiceDate|mdate:'d.m.Y'}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>{$row.number} от {$listDate|mdate:'d.m.Y'}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>{$row.clientAccount.company_full}</td>
                        <td>{$row.clientAccount.inn}/{$row.clientAccount.kpp}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>{$row.sum}</td>
                        <td>{$rowSumVat|round:2|replace:".":","}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>{$rowSumWithoutVat|round:2|replace:".":","}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                {/foreach}
                <tr>
                    <td colspan="16" style="text-align: right; font-weight: bold;">Всего</td>
                    <td>{$listSumVat|round:2|replace:".":","}</td>
                    <td></td>
                    <td></td>
                    <td>{$listSumWithoutVat|round:2|replace:".":","}</td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        {/foreach}
    {/if}

    {if $fullscreen}
</body>
</html>
{/if}


