{* https://merchant.webmoney.ru/conf/guide.asp#properties *}
<h2>Webmoney</h2>
<h3>Совершённые платежи</h3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY><TR>
<td class=header>Сумма</td><td class=header>Время</td><td class=header>Зачтено</td></tr>
{foreach from=$operations item=r name=outer}
<tr class={cycle values="even,odd"}><td>{$r.sum} {$r.currency}</td><td>{$r.ts}</td><td>{if $r.payment_id}{$r.pay_sum} {if $r.payment_rate!=1}${else}р{/if}{/if}{if $r.status!='payed'} ({$r.status}){/if}</td>
</tr>
{/foreach}
</tbody></table><br><br>

<h3>Новый платёж</h3>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0><tbody>
<form method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp">
<input type="hidden" name="LMI_PAYMENT_DESC" value="{"платеж на счет"|koi2win} {$fixclient_data.company_full|escape|koi2win} ({$fixclient_data.client}-{$fixclient_data.id})">
<input type="hidden" name="LMI_SIM_MODE" value="0">

<tr><td class=left>Клиент:</td><td>{$fixclient_data.company_full} ({$fixclient_data.client}-{$fixclient_data.id})
	<input type="hidden" name="LMI_PAYMENT_NO" value="{$wmpay.id}">
	<input type="hidden" name="keyword" value="{$wmpay.keyword}">
</td></tr><tr><td class=left>Сумма:</td><td>
	<input type=text class=text name="LMI_PAYMENT_AMOUNT" size="10" value="0.01">
	<select class=text name="LMI_PAYEE_PURSE">
{foreach from=$wmconfig item=r key=k name=outer}
	<option value="{$k}">{$r.title}</option>
{/foreach}
	</select>
</td></tr><tr><td class=left>&nbsp;</td><td>
	<input type=submit value="Оплатить через Webmoney" class=button>
</td></tr></table>

</form>
