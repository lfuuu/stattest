<h2>Платежи <FONT color="Red">{$client}</FONT></h2>

<table cellpadding="5" cellspacing="0" border="1">
<tr bgcolor="#CFD8DF">
	<TD colspan="4">Баланс на основе всех платежей и счетов</TD>
</tr>
<tr>
	<TD>Платежи всего: <b><FONT color="Red">{$payments_sum_usd}</FONT></b></TD>
	<TD>Оказано услуг на сумму: <b><FONT color="Red">{$bill_sum_usd}</FONT></b></TD>
	<TD>{if $debet > 0}Остаток на счете:{else}Задолженность клиента:{/if} <b><FONT color="Red">{$debet}</FONT></b></TD>
	<TD>Баланс: <b><font color=red>{$balance}</font></b></TD>
</tr>
<tr bgcolor="#CFD8DF">
	<TD colspan="4">Баланс на основе введенного сальдо</TD>
</tr>
<tr>

	{if access('accounts_payments','edit_saldo')}
		{assign var="readonly" value=""}
	{else}
		{assign var="readonly" value="readonly"}	
	{/if}
	
	<FORM action="modules/accounts/update_saldo.php?client={$client}" method="POST" target="_blank">
   
 
		<TD>Переплата/недоплата <b><FONT color="Red">{$saldo.saldo}</FONT></b></TD>
		<TD>Дата последней сверки <b><FONT color="Red">
		<INPUT type="text" name="date_last_saldo" value="{$saldo.date_of_last_saldo}" size="10" {$readonly}></FONT></b></TD>
		<TD colspan=2>Баланс на дату сверки <b><FONT color="Red">
		<INPUT type="text" name="fix_saldo" value="{$saldo.fix_saldo}" size="10" {$readonly}></FONT></b><br>
		<textarea name="comment" cols="40" rows="5" {$readonly}>{$saldo.comment}</textarea>
		<INPUT type="submit" name="submit" value="Обновить сальдо" {$readonly}>
		</TD>
	
	</FORM>
</tr>

</table>
<br>

{literal}
<script>
function menu_item(element,flag){
	if (flag){
		element.style.backgroundColor="#EEE0B9";
	}else {
		element.style.backgroundColor="#FFFFFF";
	};
}
</script>
{/literal}

<table border="0" cellpadding="5">
<tr bgcolor="#FFFFFF">
<td id="td1" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" >{if access('accounts_payments','w')}
<a href="modules/accounts/add_payment.php?client={$client}&saldo={$saldo.saldo}" target="_blank">Внести новый платеж</a>
{/if}</td>
<TD  id="td2" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);">
{if access('accounts_bills','w')}<A href="modules/accounts/bill_make.php?action=make&what=bill&client={$client}" target="_blank">Выставить счет</A>{/if}</TD>

<TD  id="td5" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);">
{if access('accounts_bills','w')}<A href="modules/accounts/bill_make_adv.php?client={$client}" target="_blank">Выставить счет на задаток</A>{/if}</TD>

<TD  id="td7" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);">
{if access('accounts_bills','w')}<A href="modules/accounts/bill_make_adv_phone.php?client={$client}" target="_blank">Выставить первый счет на подключение телефонии</A>{/if}
</TD>

<TD  id="td6" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);">
{if access('accounts_bills','w')}<A href="modules/accounts/bill_make_conn.php?client={$client}" target="_blank">Выставить счет на подключение</A>{/if}</TD>

<td id="td3" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" >
	<a href="modules/accounts/import_bills.php?client={$client}" >Загрузить счета из магазина</a>
</td>
<TD  id="td4" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);">
{if access('accounts_bills','w')}<A href="http://shop.mcn.ru/admin/orders/basket/index.html?client={$client}" target="_blank">Выставить счет из интернет магазина</A>{/if}
</TD>
</tr>
</table>
<br><br>


<table align="left" border="1">
	<tr bgcolor="#CFD8DF">
		<td>Дата счета</td>
		<td>Номер счета</td>
		<td>Сумма счета</td>
		<td>Переплаты<br>недоплаты<br><b><FONT color="Red">{$saldo.saldo}</FONT></b></td>
		<td width="150">Сумма платежа</td>
		<TD width="150">Дата платежа</TD>
		<td  width="150">Курс доллара</td>
		
	</tr>
	{foreach from=$bills item=bill key=key}
		<tr {if $bill.state neq "payed"} bgcolor="#FFFFD8"{/if}>
			<td>{$bill.bill_date}</td>
			<TD>
				<a href="modules/accounts/view.php?bill_no={$bill.bill_no}&client={$client}" target="_blank">{$bill.bill_no}</a><br><br>
				{if $bill.state eq 'ready'}
				<a href="modules/accounts/bill_edit.php?bill={$bill.bill_no}" target="_blank">Редактировать</a><br>	
				<a href="?module=accounts&action=accounts_payments&bill={$bill.bill_no}&todo=cancel_bill" >Аннулировать</a>
				<a href="modules/{$module}/send.php?bill_no={$bill.bill_no}&client={$bill.client}&date={$bill.bill_date}" target="_blank">Выслать</a>
				{/if}			
			</TD>
			<td align="right" valign="bottom">{$bill.sum}</td>
			<td align="right" valign="bottom"><b><FONT color="Red">{$bill.delta_sum}</FONT></b> </td>
			<td colspan="3">
			
			<table border="1" cellpadding="0" cellspacing="0" >
				{foreach from=$bill.payments item=pays key=key}
				<tr align="center">
					<td width="150">{if $pays.type eq 1  }<b>{$pays.sum_usd}</b> | {$pays.sum_rub}{else}{$pays.sum_usd} | <b>{$pays.sum_rub}</b>{/if}</td>
					<td width="150">{$pays.payment_date} | {$pays.payment_no}</td>
					<TD width="150">
						{if access('accounts_del_payment','w')}
							<form style='display:inline' method=post action='?module=accounts&action=accounts_payments&client={$client}&todo=rerate&pay_id={$pays.id}'><input type=text class=text name=rate value="{$pays.rate}" style='width:70px'><input class=button type=submit value='ok'></form>&nbsp; 
						<a href="?module=accounts&action=accounts_payments&client={$client}&todo=cancel_payment&pay_id={$pays.id}">Удалить</a>
						{else}{$pays.rate}{/if}
					</TD>
					
				</tr>
				{if $pays.comment neq ''}
				<tr>
					<td colspan="3">{$pays.comment}</td>
				</tr>
				{/if}
				{/foreach}
				<tr>
					<TD colspan="3">{$bill.payments_sum}</TD>
				</tr>
				
				
			</table>
			<table cellpadding="0">
			<tr bgcolor="#FFFFFF"><TD id="p{$key}" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" >
			{if $bill.state neq "payed"} 
			<a href="modules/accounts/make_inv2.php?bill_no={$bill.bill_no}&client={$client}" target="_blank">
			Провести счет
			</a>
			{else}
			<a href="?module=accounts&action=accounts_payments&bill={$bill.bill_no}&todo=bill_dont_payed">
			Снять признак оплаты
			</a>
			{/if}	
			</TD><TD id="k{$key}" onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);">
			<a href="modules/accounts/make_inv3.php?bill_no={$bill.bill_no}&client={$client}" target="_blank">
			Сделать счет-фактуры
			</a>
			</TD>
			</tr>
			</table>
			</td>
		</tr>
	{/foreach}
	
 </table>
