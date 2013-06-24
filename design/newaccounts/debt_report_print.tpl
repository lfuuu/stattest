<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD>
<TITLE>/ MCN | Отчет по долгам
</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=koi8-r" />
<LINK title=default href="{$PATH_TO_ROOT}main.css" type=text/css rel=stylesheet>
<LINK media=print href="{$PATH_TO_ROOT}print.css" type=text/css rel=stylesheet>
<LINK href="/favicon.ico" rel="SHORTCUT ICON">
<style>
{literal}
td{font: normal 8pt Arial;}
th{font: bold 9pt Arial;}
{/literal}
</style>
</HEAD>
<BODY text=#404040 vLink=#000099 aLink=#000000 link=#000099 bgColor=#efefef>
<h2> Отчет по долгам </h2>
<TABLE class=price cellSpacing=0 cellPadding=1 width="100%" border=1>
<tr><th>&nbsp;</th><th>метро</th><th>компания</th><th>Адрес</th><th>дата/н счёта</th><th>&nbsp;</th></tr><tr><th>&nbsp;</th><th>сумма</th><th>сальдо/Тип оплаты</th><th colspan=2>Комментарий</th><th>&nbsp;</th></tr>
{foreach from=$bills item=item key=key name=outer}<tr class={cycle values="even,odd"}><!--{cycle values="even,odd"}-->
<td width="1%">{$smarty.foreach.outer.iteration}</td>
<td nowrap width="10%"> {$item.metro}</td>
<td width="12%" style='font-size:85%'{if $item.nal!='beznal'} bgcolor='#{if $item.nal == "nal"}FFC0C0{else}C0C0FF{/if}'{/if} color=black>{$item.client}<br>{$item.company}</td>
<td width="17%">{$item.address}</td>
<td width="10%">{$item.bill_date}<br>{$item.bill_no}</td>
<td width="50%" valign=top>Получил</td>
</tr><tr class={cycle values="even,odd"}><td width="1%">&nbsp;</td>
<td nowrap width="10%" align=right>{$item.sum|round:2} {if $item.currency=='USD'}$
{if $item.gen_bill_rur!=0}<br><span style='font-size:85%' title='Сумма счёта, {$item.gen_bill_date}'>b {$item.gen_bill_rur} р</span>{/if}{else}р{/if} / {$item.sum_full|round:2}{if $item.currency=='USD'}${else}р.{/if}</td>
<td nowrap width="12%" align=center{if $item.bill_nal!='beznal'} bgcolor='#{if $item.bill_nal == "nal"}FFC0C0{else}C0C0FF{/if}'{/if}>{$item.debt.sum|round:2}{if $item.debt.currency=='USD'}${else}р.{/if}/{$item.bill_nal}</td>
<td width="27%" colspan=2 align=left>{$item.payment_comment}&nbsp;</td>
<td width="50%" valign=top>Отказ</td>
</tr>
<tr><td colspan=7 style="padding: 0 0 0 0; margin: 0 0 0 0;"><hr noshade style="padding:0 0 0 0;margin:0 0 0 0;"></tr>{/foreach}

<tr style='background:#FFFFFF'>
<td colspan=3 align=right><b>Всего по долларовым счетам:</b></td>
<td align=right>{$bills_total_USD.sum|round:2} $</td>
<td align=center>{$bills_total_USD.saldo|round:2} $ </td>
<td>&nbsp;</td>
</tr>
<tr style='background:#FFFFFF'>
<td colspan=3 align=right><b>Всего по рублёвым счетам:</b></td>
<td align=right>{$bills_total_RUR.sum|round:2} р</td>
<td align=center>{$bills_total_RUR.saldo|round:2} р</td>
<td>&nbsp;</td>
</tr>
</TABLE>
</body>
</html>
