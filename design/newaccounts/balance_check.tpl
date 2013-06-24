{if $fullscreen}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Акт сверки {$fixclient_data.client}</TITLE>
<META http-equiv=Content-Type content="text/html; charset=koi8-r">
{literal}<STYLE>
.price {
	font-size:15px;
}
body {
    color: black;
}
td {
    color: black;
}
thead tr td {
	font-weight:bold;
}
h2 {
	text-align:center;
}
h3 {
	text-align:center;
}
</STYLE>{/literal}
</HEAD>


<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" >
<h2>АКТ СВЕРКИ</h2>
<h3 style="color: black;">взаимных расчетов по состоянию на {$date_to_val|mdate:"d.m.Y г."}<br>
между {$company_full}<br>
и
{$firma.name}
</h3>

<TABLE class=price cellSpacing=0 cellPadding=2 border=1>
{else}
<form style='display:inline' action='?'>
	<input type=hidden name=module value=newaccounts>
	<input type=hidden name=action value=balance_check>
От:	<input type=text name=date_from value='{$date_from}' class=text>
До:	<input type=text name=date_to value='{$date_to}' class=text>
Начальное сальдо: <input type=text name=saldo value='{$saldo}' class=text style='width:35px'>
Полный экран: <input type=checkbox name=fullscreen value='1'>
<input type=submit value='Показать' class=button>
</form>
<h2>Акт сверки по клиенту {$fixclient_data.client}</h2>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
{/if}
<thead>
<tr ><td width=50% colspan=4>По данным {$firma.name}, руб.</td><td width=50% colspan=4>По данным {$company_full}, руб.</td></tr>
<tr><td width=4%>&#8470; п/п</td><td width=24%>Наименование операции,<br>документы</td><td width=11%>Дебет</td><td width=11%>Кредит</td>
<td width=4%>&#8470; п/п</td><td width=24%>Наименование операции,<br>документы</td><td width=11%>Дебет</td><td width=11%>Кредит</td></tr></thead><tbody>
{foreach from=$data item=item name=outer}
<tr{if !$fullscreen} class={cycle values="even,odd"}{/if}>
	<td>{$smarty.foreach.outer.iteration}</td>
	<td>{if $item.type=='saldo'}
		Сальдо на {$item.date|mdate:"d.m.Y"}
{elseif $item.type=='inv'}
	{if $item.inv_num == 3}
		Акт передачи оборудования под залог{else}
		{if $item.inv_num!=4}
			Акт
		{else}
			Накладная
		{/if}
	{/if} <nobr>({$item.date|mdate:"d.m.Y"},</nobr> <nobr>&#8470;{$item.inv_no}{*if $fullscreen}{$item.inv_no}{else}<a href='{$LINK_START}module=newaccounts&action=bill_print&bill={$item.bill_no}&obj=invoice&source={$item.inv_num}'>{$item.inv_no}</a>{/if*})</nobr>
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
</tbody></table>

<font style="color: black;">По данным  {$firma.name} на {$date_to_val|mdate:"d.m.Y г."},

{if $zalog} с учетом платежей полученных в обеспечение исполнения обязательств по договору:
<table>
{foreach from=$zalog item=z name=zalog}
<tr><td>{$smarty.foreach.zalog.iteration}.&nbsp;</td><td>{$z.date|mdate:"d.m.Y"}, &#8470;{$z.inv_no} ({$z.items})</td><td>{$z.sum_income|round:2|replace:".":","} рубл{$z.sum_income|rus_fin:'ь':'я':'ей'}</td></tr>
{/foreach}
</table>

{else}

{/if}

&nbsp;задолженность
{if $ressaldo.sum_income>0.0001}
	в пользу {$firma.name} составляет {$ressaldo.sum_income|round:2|replace:".":","} рубл{$ressaldo.sum_income|rus_fin:'ь':'я':'ей'}
{elseif $ressaldo.sum_outcome>0.0001}
	в пользу {$company_full} составляет {$ressaldo.sum_outcome|round:2|replace:".":","} рубл{$ressaldo.sum_outcome|rus_fin:'ь':'я':'ей'}
{else}
	отсутствует
{/if}
</font>
{*if $zalog}({$formula}){/if*}.
{if $fullscreen}
    <div>
    <table border="0" cellpadding="0" cellspacing="5">
      <tr>
        <td><p>От {$firma.name}</td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td><p>От {$company_full}</td>
</tr>
<tr><td>
        <br><br>Руководитель организации ___________________
    {if $fixclient_data.firma=='ooomcn'}Бирюкова Н.В.
{elseif $fixclient_data.firma == "all4net"}Пыцкая М. А.
{elseif $fixclient_data.firma == "ooocmc"}Надточеева Н.А.
{elseif $fixclient_data.firma == "markomnet_new"}Мазур Т.В.
{else}Мельников А.К.{/if} <br><br>
    </td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td><br><br>______________________________<br><br></td>
      </tr>
      <tr>
        <td align="center"><small>(подпись)</small></td>
    <td></td>
        <td align="center"><small>(подпись)</small></td>
      </tr>
      <tr>
        <td align="center"><br><br>М.П.</td>
    <td></td>
        <td align="center"><br><br>М.П.</td>
      </tr>
    </table>
    </div>


</body>
</html>
{/if}
