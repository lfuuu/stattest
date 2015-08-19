{if $fullscreen}
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
    <html>
        <head>
        <title>Книга продаж</title>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
        {literal}
            <style type="text/css">
            .price {
                font-size:14px;
            }
            thead tr td {
                font-weight:bold;
            }
            thead tr td.s {
                padding:1px 1px 1px 1px;
                font-size:12px;
            }
            h2 {
                text-align:center;
            }
            h3 {
                text-align:center;
            }
            </style>
        {/literal}
    </head>

    <body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" >
        <h2>КНИГА ПРОДАЖ</h2>
        Продавец     __________________________________________________________________________________________________________<br />
        Идентификационный номер и код причины постановки на учет налогоплательщика-продавца     __________________________________________________________________________________________________________<br>
        Продажа за период с {$date_from_val|mdate:"d месяца Y г."} по {$date_to_val|mdate:"d месяца Y г."}<br />

        <table class="price" cellSpacing="0" cellPadding="2" border="1">
{else}
    <form style="display:inline" action=""?">
        <input type="hidden" name="module" value="newaccounts" />
        <input type="hidden" name="action" value="balance_sell" />
        От: <input id="date_from" type="text" name="date_from" value="{$date_from}" class="text" />
        До: <input id="date_to" type="text" name="date_to" value="{$date_to}" class="text" /><br />
        Компания:
            <select class="text" name="firma">
                <option value="mcn_telekom"{if $firma == "mcn_telekom"} selected="selected"{/if}>ООО "МСН Телеком"</option>
                <option value="all4geo"{if $firma == "all4geo"} selected="selected"{/if}>ООО "Олфогео"</option>
                <option value="mcn"{if $firma == "mcn"} selected="selected"{/if}>ООО "Эм Си Эн"</option>
                <option value="markomnet_new"{if $firma == "markomnet_new"} selected="selected"{/if}>ООО "МАРКОМНЕТ"</option>
                <option value="markomnet"{if $firma == "markomnet"} selected="selected"{/if}>ООО "МАРКОМНЕТ" (старый)</option>
                <option value="ooomcn"{if $firma == "ooomcn"} selected="selected"{/if}>ООО "МСН"</option>
                <option value="all4net"{if $firma == "all4net"} selected="selected"{/if}>ООО "ОЛФОНЕТ"</option>
                <option value="ooocmc"{if $firma == "ooocmc"} selected="selected"{/if}>ООО "Си Эм Си"</option>
            </select>

        Полный экран: <input type="checkbox" name="fullscreen" value="1" />&nbsp;
        в Excel (csv): <input type="checkbox" name="csv" value="1" /><br />
        <input type="submit" value="Показать" class="button" name="do" />
    </form>
    <h2>Книга продаж</h2>
    <table class="price" cellspacing="4" cellpadding="2" border="0">
{/if}

        <thead>
            <tr>
                <td width="5%" rowspan="4" class="s">№<br />п/п</td>
                <td width="5%" rowspan="4" class="s">Код<br />вида<br />опера-<br />ции</td>
                <td width="10%" rowspan="4" class="s">Дата и номер счета-фактуры продавца</td>
                <td width="*" rowspan="4">Наименование покупателя</td>
                <td width="5%" rowspan="4">ИНН/КПП<br />покупателя</td>
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
                    <td><nobr>{$r.inv_no};</nobr> <nobr>{$r.inv_date|mdate:"d.m.Y"}</nobr></td>
                    <td class="s">{$r.company_full}&nbsp;</td>
                    <td>{if $r.inn}{$r.inn}{if $r.type == 'org'}/{if $r.kpp}{$r.kpp}{/if}{/if}{else}&nbsp;{/if}</td>
                    <td>{$r.type}</td>
                    <td>{$r.contract}</td>
                    <td>{$r.contract_status}</td>
                    <td>{if $r.payments}{$r.payments}{else}&nbsp;{/if}</td>
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

{if $fullscreen}
        </body>
    </html>
{/if}
