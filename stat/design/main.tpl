<h2>Счета</h2>
<table  class="menu2" cellpadding="10" cellspacing="0" border="1">
 <tr class="trmenu2">
 	<td>Выставить новые счета</td>
 	<td>Показать счета</td>
 	
 </tr>
 <tr>
 	<td colspan="2">Счета клиента: &nbsp;&nbsp;<b>{$customer}</b></td>
 </tr>
<tr class="trmenu2">
 	<td>
 		<form action="?module=accounts&action=accounts_bills&todo=newbills" method="post">
         		<table cellpadding="0" cellspacing="0" border="0">
         			
         			<tr>
         				<td>Период оплаты по факту</td>
         				<td>С <input type="text" name="fact_from" value="{$fact_from}" size="10" maxlength="10">
         				-<input type="text" name="fact_to" value="{$fact_to}" size="10" maxlength="10">
         				</td>
         			</tr>
         			<tr>
         				<td>Период по предоплате </td>
         				<td>С <input type="text" name="pre_from" value="{$pre_from}" size="10" maxlength="10">
         				-<input type="text" name="pre_to" value="{$pre_to}" size="10" maxlength="10">
         				</td>
         			</tr>
         			<tr>
         				<td colspan=2>
         					<input type="submit" vALUE="Выписать счета">
         				</td>
         			</tr>
         		</table>
 		</form>
 	
 	
 	</td>
 	<td>
 		<form action="?module=accounts&action=accounts_bills&todo=showbills" method="post">
         		<table cellpadding="0" cellspacing="0" border="0">
         			<tr>
         				<td>Найти счет номер</td>
         				<td><input type="text" name="bill_no" size="10"></td>
         			</tr>
         			<tr>
         				<td>Найти счета за период</td>
         				<td>Год:<input type="text" name="year" size="4" value="{$this_year}">
         				Месяц:<input type="text" name="month" size="4" value="{$this_month}"></td>
         			
         			</tr>
         			<tr>
         				
         				<td>Только неоплаченные:</td><td><input type="checkbox" name="notpaied"></td>
         				
         				
         			</tr>
         			<tr>
         				<td>Счета колллективной точки:</td><td><input type="text" name="router"></td>
         			</tr>
         			<tr>
         				<td colspan=2>
         					<input type="submit" vALUE="Показать счета">
         				</td>
         			</tr>
         			
         		</table>
 		</form>
 	</td>
 	
 </tr>
</table>