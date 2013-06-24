<h2>Счета</h2>
Счета клиента: &nbsp;&nbsp;<b>{$customer}</b>
<form action="?module=accounts&action=accounts_bills&todo=showbills" method="post">
<table cellspacing=0 cellpadding=10 border=1><tr>
<th>Найти счет номер</th>
<th>Найти счета за период</th>
</tr><tr><td>
			<input type="text" name="bill_no" size="10">
</td><td>
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td>Период:</td><td>
				<select name=year>
					<option value=''{if $this_year==''} selected{/if}></option>
					<option value=2003{if $this_year==2003} selected{/if}>2003</option>
					<option value=2004{if $this_year==2004} selected{/if}>2004</option>
					<option value=2005{if $this_year==2005} selected{/if}>2005</option>
					<option value=2006{if $this_year==2006} selected{/if}>2006</option>
					<option value=2007{if $this_year==2007} selected{/if}>2007</option>
				</select>
				<select name=month>
					<option value=''{if $this_month==''} selected{/if}></option>
					<option value=1{if $this_month==1} selected{/if}>январь</option>	
					<option value=2{if $this_month==2} selected{/if}>февраль</option>	
					<option value=3{if $this_month==3} selected{/if}>март</option>	
					<option value=4{if $this_month==4} selected{/if}>апрель</option>	
					<option value=5{if $this_month==5} selected{/if}>май</option>	
					<option value=6{if $this_month==6} selected{/if}>июнь</option>	
					<option value=7{if $this_month==7} selected{/if}>июль</option>	
					<option value=8{if $this_month==8} selected{/if}>август</option>	
					<option value=9{if $this_month==9} selected{/if}>сентябрь</option>	
					<option value=10{if $this_month==10} selected{/if}>октябрь</option>	
					<option value=11{if $this_month==11} selected{/if}>ноябрь</option>	
					<option value=12{if $this_month==12} selected{/if}>декабрь</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Только неоплаченные:</td><td><input type="checkbox" name="notpaid" value="1"></td>
		</tr>
		<tr>
			<td>Показывать удалённые счета:</td><td><input type="checkbox" name="cancelled" value="1"></td>
		</tr>
		<tr>
			<td>Счета колллективной точки:</td><td>
			<select name=router>
				<option value='' selected></option>
{foreach from=$routers item=item name=outer}
				<option value={$item.router}>{$item.router}</option>
{/foreach}
			</select>			
		</tr>
		<tr>
			<td>Менеджер:</td><td>
			<select name=manager>
{foreach from=$managers item=item name=outer}
				<option value='{$item.user}'{if $item.user==$manager} selected{/if}>{$item.name} ({$item.user})</option>
{/foreach}
				<option value=''{if !$manager} selected{/if}>любой</option>
			</select>			
		</tr>
	</table>
</td></tr><tr>
<td colspan=2 align=center><input type="submit" vALUE="Показать счета"></td>
</tr></table>
</form>
 		
 		
{if access('accounts_bills','auto_bills')}
<form  action="modules/accounts/bill_make_auto.php" method="post" target="_blank">
<input type=hidden name='todo' value='auto_bills'>
<br> <hr>
<table >
    <tr>
	<td colspan="2" bgcolor="Red" align="center"><b>Выставление счетов  всем клиентам</b></td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    Дата
	</td>

	<td BGCOLOR="#C5D6E3">
	    <input type=text name='date_d' value='1' size=2 maxlength=2>/
	    <input type=text name='date_m' value='{$pre_month}' size=2 maxlength=2>/
	    <input type=text name='date_y' value='{$pre_year}' size=4 maxlength=4>
	</td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    Период по факту
	</td>

	<td BGCOLOR="#C5D6E3">
	    <input type=text name='period_f_m' value='{$fact_month}' size=2 maxlength=2>/
	    <input type=text name='period_f_y' value='{$fact_year}' size=4 maxlength=4>
	</td>
    </tr>
    <tr>
	<td BGCOLOR="#C5D6E3" align=right>
	    Период по предоплате
	</td>
	<td BGCOLOR="#C5D6E3">

	    <input type=text name='period_pre_m' value='{$pre_month}' size=2 maxlength=2>/
	    <input type=text name='period_pre_y' value='{$pre_year}' size=4 maxlength=4>
	</td>
    </tr>
    <tr>
    	<td>Компенсация за непредоставление услуг, часы</td>
    	<td><INPUT type="text" name="comp" value="0" size="3" maxlength="3"></td>
    </tr>
    <tr>
    	<td colspan=2><input type=checkbox name=must_pay value=1 checked>Обязателен к оплате</td>
	</tr>   
</table>

<br>
<center>
<input type=submit value='Выставить'>
</center>
</form><br>
<a href="?module=accounts&action=accounts_bills&todo=auto_bills_print" target="_blank">
печать счетов
</a>
{/if}