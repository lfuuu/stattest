{if $fullscreen}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Книга продаж</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
{literal}<STYLE>
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
</STYLE>{/literal}
</HEAD>


<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" >
<h2>КНИГА ПРОДАЖ</h2>
Продавец     __________________________________________________________________________________________________________<br>
Идентификационный номер и код причины постановки на учет налогоплательщика-продавца     __________________________________________________________________________________________________________<br>
Продажа за период с {$date_from_val|mdate:"d месяца Y г."} по {$date_to_val|mdate:"d месяца Y г."}<br>

<TABLE class=price cellSpacing=0 cellPadding=2 border=1>
{else}
<form style='display:inline' action='?'>
	<input type=hidden name=module value=newaccounts>
	<input type=hidden name=action value=balance_sell>
От:	<input id=date_from type=text name=date_from value='{$date_from}' class=text>
До:	<input id=date_to type=text name=date_to value='{$date_to}' class=text><br>
Метод оплаты: <select name=paymethod>
	<option value=beznal{if $paymethod=='beznal'} selected{/if}>beznal</option>
	<option value=nal{if $paymethod=='nal'} selected{/if}>nal</option>
	<option value=prov{if $paymethod=='prov'} selected{/if}>prov</option>
	</select><br>
Компания:<select class="text" name="firma">
        <option value="mcn_telekom"{if $firma == "mcn_telekom"} selected{/if}>ООО "МСН Телеком"</option>
        <option value="all4geo"{if $firma == "all4geo"} selected{/if}>ООО "Олфогео"</option>
        <option value="mcn"{if $firma == "mcn"} selected{/if}>ООО "Эм Си Эн"</option>
        <option value="markomnet_new"{if $firma == "markomnet_new"} selected{/if}>ООО "МАРКОМНЕТ"</option>
        <option value="markomnet"{if $firma == "markomnet"} selected{/if}>ООО "МАРКОМНЕТ" (старый)</option>
        <option value="ooomcn"{if $firma == "ooomcn"} selected{/if}>ООО "МСН"</option>
        <option value="all4net"{if $firma == "all4net"} selected{/if}>ООО "ОЛФОНЕТ"</option>
        <option value="ooocmc"{if $firma == "ooocmc"} selected{/if}>ООО "Си Эм Си"</option>
        </select>

Полный экран: <input type=checkbox name=fullscreen value='1'>&nbsp;
в Excel (csv): <input type=checkbox name=csv value='1'><br>
Счета: <select name=payfilter><option value='1'{if $payfilter=='1'} selected{/if}>полностью оплаченные</option>
<option value='3'{if $payfilter=='3'} selected{/if}>полностью или частично оплаченные</option>
<option value='0'{if $payfilter=='0'} selected{/if}>все</option></select>
<input type=submit value='Показать' class=button name="do">
</form>
<h2>Книга продаж</h2>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
{/if}
<thead><tr>
	<td width=10% rowspan=4 class=s>Дата и номер счета-фактуры продавца</td>
	<td width=* rowspan=4>Наименование покупателя</td>
	<td width=5% rowspan=4>ИНН покупателя</td>
	<td width=5% rowspan=4>КПП покупателя</td>
	<td width=5% rowspan=4>Тип ЛС</td>
	<td width=5% rowspan=4 class=s>Дата оплаты счета-фактуры продавца</td>
	<td width=5% rowspan=4>Всего продаж, включая НДС</td>
	<td width=40% colspan=8>В том числе</td>
</tr><tr>
	<td width=53% colspan=7>продажи, облагаемые налогом по ставке</td>
	<td width=5% rowspan=3 class=s>продажи, освобождаемые от налога</td>
</tr><tr>
	<td colspan=2 class=s>18 процентов (5)</td>
	<td colspan=2 class=s>10 процентов (6)</td>
	<td rowspan=2 class=s>0 процентов</td>
	<td colspan=2 class=s>20 процентов* (8)</td>
</tr><tr>
	<td class=s>стоимость продаж<br>без НДС</td>
	<td class=s>сумма НДС</td>
	<td class=s>стоимость продаж<br>без НДС</td>
	<td class=s>сумма НДС</td>
	<td class=s>стоимость продаж<br>без НДС</td>
	<td class=s>сумма НДС</td>
</tr></thead><tbody>
{foreach from=$data item=r name=outer}
<tr class={cycle values="even,odd"}>
	<td><nobr>{$r.inv_no},</nobr> <nobr>{$r.inv_date|mdate:"d.m.Y"}</nobr></td>
	<td class=s>{$r.company_full}&nbsp;</td>
    <td>{if $r.inn}{$r.inn}{else}&nbsp;{/if}</td>
    <td>{if $r.kpp}{$r.kpp}{else}&nbsp;{/if}</td>
    <td>{$r.type}</td>
	<td>{if $r.payment_date}{$r.payment_date|mdate:"d.m.Y"}{else}&nbsp;{/if}</td>
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
<tr class={cycle values="even,odd"}>
	<td colspan=5 align=right>Всего:</td>
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
</tbody></table>
<script>
optools.DatePickerInit();
</script>
{if $fullscreen}
</body>
</html>
{/if}
