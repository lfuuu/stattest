<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
    <TITLE>Financial statement {$fixclient_data.client}</TITLE>
    <META http-equiv=Content-Type content="text/html; charset=utf-8">
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
    <h2>Financial statement</h2>
    <h3 style="color: black;">on {$date_to_val|mdate:"d.m.Y"} between {$company_full}, account ID: {$client_id}<br>and {$firma.name}</h3>
    <TABLE class=price cellSpacing=0 cellPadding=2 border=1>
        <thead>
            <tr>
                <td width=50% colspan=4>Based on data {$firma.name}, EUR</td>
                <td width=50% colspan=4>Based on data from {$company_full}, EUR</td>
            </tr>
            <tr>
                <td width=4%>&#8470;  </td>
                <td width=36%>Operations documents</td>
                <td width=5%>Debit</td>
                <td width=5%>Credit</td>
                <td width=4%>&#8470;  </td>
                <td width=24%>Operations documents</td>
                <td width=11%>Debit</td>
                <td width=11%>Credit</td>
            </tr>
        </thead>
        <tbody>
            {foreach from=$data item=item name=outer}
                <tr{if !$fullscreen} class={cycle values="even,odd"}{/if}>
                    <td>{$smarty.foreach.outer.iteration}</td>
                    <td>{if $item.type=='saldo'}Balance on {$item.date|mdate:"d.m.Y"}
                        {elseif $item.type=='inv'}
                            {if $item.inv_num == 3}
                                The act of transferring equipment on bail
                            {else}
                                  {if $item.inv_num!=4}Invoice{else}Waybill{/if}
                            {/if} 
                            <nobr>({$item.date|mdate:"d.m.Y"},</nobr> <nobr>&#8470;{$item.inv_no})</nobr>
                        {elseif $item.type=='pay'}
                            Payment <nobr>({$item.date|mdate:"d.m.Y"},</nobr> <nobr>&#8470;{$item.pay_no})</nobr>
                        {elseif $item.type=='creditnote'}
                            Credit-node on <nobr>{$item.date|mdate:"d.m.Y"}</nobr>
                        {elseif $item.type=='total'}
                            Period transactions
                        {/if}
                    </td>
                    <td align=right>{if isset($item.sum_income) && ($item.sum_income || $item.type =='saldo')}{$item.sum_income|round:2|replace:".":","}{else}&nbsp;{/if}</td>
                    <td align=right>{if isset($item.sum_outcome) && ($item.sum_outcome || $item.type =='saldo')}{$item.sum_outcome|round:2|replace:".":","}{else}&nbsp;{/if}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            {/foreach}
        </tbody>
    </table>

    <font style="color: black;">
        Based on data from {$firma.name} on {$date_to_val|mdate:"d.m.Y"},

        {if $zalog} с учетом платежей полученных в обеспечение исполнения обязательств по договору:
            <table>
                {foreach from=$zalog item=z name=zalog}
                    <tr><td>{$smarty.foreach.zalog.iteration}.&nbsp;</td><td>{$z.date|mdate:"d.m.Y"}, &#8470;{$z.inv_no} ({$z.items})</td><td>{$z.sum_income|money_currency:$currency}</td></tr>
                {/foreach}
            </table>
        {/if}
    
        {if $ressaldo.sum_income>0.0001}
            the indebtedness to {$firma.name} is {$ressaldo.sum_income|money_currency:$currency}
        {elseif $ressaldo.sum_outcome>0.0001}
            the indebtedness to {$company_full} is {$ressaldo.sum_outcome|money_currency:$currency}
        {else}
            is no debt
        {/if}
    </font>.

    <div>
        <table border="0" cellpadding="0" cellspacing="5">
            <tr>
                <td colspan="3"><p>From {$firma.name}</p></td>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td><p>From {$company_full}</p></td>
            </tr>
            <tr><td colspan="5">&nbsp;</td></tr>
            <tr><td colspan="5">&nbsp;</td></tr>
            <tr>
                <td>{if $sign == 'istomina'}Accountant{else}Head of the organization{/if}</td>
                <td>___________________</td>
                <td>{if $sign == 'istomina'}Истомина И.В. Decree N24 on 01.08.2013{else}{$firm_director.name}{/if}</td>
                <td></td>
                <td>______________________________</td>
            </tr>
            <tr>
                <td></td>
                <td align="center"><small>(signature)</small></td>
                <td></td>
                <td></td>
                <td align="center"><small>(signature)</small></td>
              </tr>
        </table>
    </div>
    {if $sign == 'istomina'}
        <div style="position:absolute; z-index:100; left:190px; margin-left:-110px;margin-top:-100px;">
            <img src="{$WEB_PATH}images/sign_istomina.png" width="120px" height="62px" />
        </div>
        {if $firma.src}
            <div style="position:absolute; z-index:100; left:200px; margin-left:-160px;{if $firma.height}margin-top:-{math equation="x*0.75" x=$firma.height}{/if}">
                <img src="{$WEB_PATH}images/{$firma.src}"{if $firma.width} width="{$firma.width}" height="{$firma.height}"{/if} />
            </div>
        {/if}
    {elseif $sign == 'director'}
        <div style="position:absolute; z-index:100; left:200px; margin-left:-80px;margin-top:{if $firm_director.sign.src == 'sign_vav.png' || $firm_director.sign.src == 'sign_bnv.png'}-140px;{else}-100px;{/if}">
            <img src="{$WEB_PATH}images/{$firm_director.sign.src}"  border="0" alt="" align="top"{if $firm_director.sign.width} width="{$firm_director.sign.width}" height="{$firm_director.sign.height}"{/if}>
        </div>
        {if $firma.src}
            <div style="position:absolute; z-index:100; left:200px; margin-left:-120px;{if $firma.height}margin-top:-{math equation="x*0.75" x=$firma.height}{/if}">
                <img src="{$WEB_PATH}images/{$firma.src}"{if $firma.width} width="{$firma.width}" height="{$firma.height}"{/if} />
            </div>
        {/if}
    {/if}
</body>
</html>
