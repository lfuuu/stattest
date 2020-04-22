<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Счёт &#8470;{$bill.bill_no}</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<LINK title=default href="{if $is_pdf == '1'}{$WEB_PATH}{else}{$PATH_TO_ROOT}{/if}bill.css" type="text/css" rel="stylesheet">
</HEAD>
<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" >
	<center><h2>Товарный чек &#8470;{$bill.bill_no}</h2></center>
	<center><h4>{if !$without_date_date}{$bill.ts|mdate:"от d.m.Y г."}{else}{$without_date_date|mdate:"от d.m.Y г."}{/if}</h4></center>

	<table border="1" align="center" width="100%">
		<tr style="text-align: center">
			<td>&#8470;</td>
			<td>Артикул</td>
			<td>Наименование</td>
			<td>Количество</td>
			<td>Цена за шт.{if !$firm.is_simple_tax_system} с НДС{/if}</td>
			<td>Сумма{if !$firm.is_simple_tax_system} с НДС{/if}</td>
		</tr>
		{foreach from=$bill_lines item='l' key='k'}
			{assign var='k' value=$k+1}
			{math assign='price' equation="x / y" x=$l.sum y=$l.amount}
		<tr>
			<td align="center">{$k}</td>
			<td align="left">{if isset($1c_lines[$k])}{$1c_lines[$k].articul}{/if}&nbsp;</td>
			<td align="left">{$l.item}&nbsp;{if $serials && isset($serials[$l.code_1c])}<br>(с/н: {foreach from=$serials[$l.code_1c] item=s name=foreach_ss}{if $smarty.foreach.foreach_ss.iteration > 1},{/if} {$s}{/foreach}){/if}</td>
			<td align="center">{$l.amount|round:0} шт.</td>
			<td align="center">{$price|round:4} руб.</td>
			<td align="center">{$l.sum|round:2} руб.</td>
		</tr>
		{/foreach}
	</table>
	<br />
	<p>Сумма: {$bill.sum|round:2} руб. (сумма прописью: {$bill.sum|wordify:'RUB'})</p>
	<p>Подпись продавца ____________________</p>
	<br /><br />
	<p>Товар надлежащего качества может быть возвращен продавцу в течении 7 дней<br /><br />
	Подпись: ____________________</p>

{if $onlime_order}
<br>________________________________________________</br>
<b>Onlime</b>
Купон: {$onlime_order->coupon}<br>
Секретный код: {$onlime_order->seccode}<br>
Проверочный код: {$onlime_order->vercode}
{/if}
</body>
</html>
