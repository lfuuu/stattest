<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
    <TITLE>Акт сверки {$fixclient_data.client}</TITLE>
    <META http-equiv=Content-Type content="text/html; charset=koi8-r">
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
    p {font-family: 'Times New Roman'; font-size: 8pt;}
    td {font-family: 'Times New Roman'; font-size: 8pt;}
    th {font-family: Verdana; font-size: 6pt;}
    small {font-size: 6.5pt;}
    strong {font-size: 6.5pt;}
    </STYLE>
    {/literal}
</HEAD>


<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" >
    <h2>АКТ СВЕРКИ</h2>
    <h3 style="color: black;">взаимных расчетов по состоянию на {$date_to_val|mdate:"d.m.Y г."}<br>между {$company_full}<br>и {$firma.name}</h3>
    <TABLE class=price cellSpacing=0 cellPadding=2 border=1>
        <thead>
            <tr>
                <td width=50% colspan=4>По данным {$firma.name}, руб.</td>
                <td width=50% colspan=4>По данным {$company_full}, руб.</td>
            </tr>
            <tr>
                <td width=4%>&#8470; п/п</td>
                <td width=36%>Наименование операции,<br>документы</td>
                <td width=5%>Дебет</td>
                <td width=5%>Кредит</td>
                <td width=4%>&#8470; п/п</td>
                <td width=24%>Наименование операции,<br>документы</td>
                <td width=11%>Дебет</td>
                <td width=11%>Кредит</td>
            </tr>
        </thead>
        <tbody>
            {foreach from=$data item=item name=outer}
                <tr{if !$fullscreen} class={cycle values="even,odd"}{/if}>
                    <td>{$smarty.foreach.outer.iteration}</td>
                    <td>{if $item.type=='saldo'}Сальдо на {$item.date|mdate:"d.m.Y"}
                        {elseif $item.type=='inv'}
                            {if $item.inv_num == 3}
                                Акт передачи оборудования под залог
                            {else}
                                  {if $item.inv_num!=4}Акт{else}Накладная{/if}
                            {/if} 
                            <nobr>({$item.date|mdate:"d.m.Y"},</nobr> <nobr>&#8470;{$item.inv_no})</nobr>
                        {elseif $item.type=='pay'}
                            Оплата <nobr>({$item.date|mdate:"d.m.Y"},</nobr> <nobr>&#8470;{$item.pay_no})</nobr>
                        {elseif $item.type=='total'}
                            Обороты за период
                        {/if}
                    </td>
                    <td align=right>{if isset($item.sum_income)}{$item.sum_income|round:2|replace:".":","}{else}&nbsp;{/if}</td>
                    <td align=right>{if isset($item.sum_outcome) && ($item.sum_outcome != 0 || $item.type =='saldo')}{$item.sum_outcome|round:2|replace:".":","}{else}&nbsp;{/if}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            {/foreach}
        </tbody>
    </table>

    <font style="color: black;">
        По данным  {$firma.name} на {$date_to_val|mdate:"d.m.Y г."},

        {if $zalog} с учетом платежей полученных в обеспечение исполнения обязательств по договору:
            <table>
                {foreach from=$zalog item=z name=zalog}
                    <tr><td>{$smarty.foreach.zalog.iteration}.&nbsp;</td><td>{$z.date|mdate:"d.m.Y"}, &#8470;{$z.inv_no} ({$z.items})</td><td>{$z.sum_income|round:2|replace:".":","} рубл{$z.sum_income|rus_fin:'ь':'я':'ей'}</td></tr>
                {/foreach}
            </table>
        {/if}
    
        &nbsp;задолженность
        {if $ressaldo.sum_income>0.0001}
            в пользу {$firma.name} составляет {$ressaldo.sum_income|round:2|replace:".":","} рубл{$ressaldo.sum_income|rus_fin:'ь':'я':'ей'}
        {elseif $ressaldo.sum_outcome>0.0001}
            в пользу {$company_full} составляет {$ressaldo.sum_outcome|round:2|replace:".":","} рубл{$ressaldo.sum_outcome|rus_fin:'ь':'я':'ей'}
        {else}
            отсутствует
        {/if}
    </font>.

    <div>
        <table border="0" cellpadding="0" cellspacing="5">
            <tr>
                <td colspan="3"><p>От {$firma.name}</p></td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td><p>От {$company_full}</p></td>
            </tr>
            <tr><td colspan="5">&nbsp;</td></tr>
            <tr><td colspan="5">&nbsp;</td></tr>
            <tr>
                <td>{if $sign == 'istomina'}Бухгалтер{else}Руководитель организации{/if}</td>
                <td>___________________</td>
                <td>{if $sign == 'istomina'}Истомина И.В. Приказ N24 от 01.08.2013{else}{$firm_director.name}{/if}</td>
                <td></td>
                <td>______________________________</td>
            </tr>
            <tr>
                <td></td>
                <td align="center"><small>(подпись)</small></td>
                <td></td>
                <td></td>
                <td align="center"><small>(подпись)</small></td>
              </tr>
              <tr>
                <td></td>
                <td align="center"><br><br>М.П.</td>
                <td></td>
                <td></td>
                <td align="center"><br><br>М.П.</td>
            </tr>
        </table>
    </div>
    {if $sign == 'istomina'}
        <div style="position:absolute; z-index:100; left:190px; margin-left:-110px;margin-top:-120px;">
            <img src="{$WEB_PATH}images/sign_istomina.png" width="120px" height="62px" />
        </div>
        <div style="position:absolute; z-index:100; left:200px; margin-left:-160px;margin-top:0px;">
            <img style='{$firma.style}' src="{$WEB_PATH}images/{$firma.src}"{if $firma.width} width="{$firma.width}" height="{$firma.height}"{/if} />
        </div>
    {elseif $sign == 'director'}
        <div style="position:absolute; z-index:100; left:200px; margin-left:-80px;margin-top:-125px;">
            <img src="{$WEB_PATH}images/{$firm_director.sign.src}"  border="0" alt="" align="top"{if $firm_director.sign.width} width="{$firm_director.sign.width}" height="{$firm_director.sign.height}"{/if}>
        </div>
        <div style="position:absolute; z-index:100; left:200px; margin-left:-120px;margin-top:0px;">
            <img style='{$firma.style}' src="{$WEB_PATH}images/{$firma.src}"{if $firma.width} width="{$firma.width}" height="{$firma.height}"{/if} />
        </div>
    {/if}
</body>
</html>
