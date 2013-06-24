<h2>Массовые операции со счетами</h2>
<form action='?' method=get>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=bill_mass>
<input type=hidden name=obj value=print>
Напечатать
<input type=checkbox name=do_bill checked>счета
<input type=checkbox name=do_inv>счет-фактуры
<input type=checkbox name=do_akt>акты
<select name=date><option value='month'>созданные в текущем месяце</option><option value='today'>созданные сегодня</option><option value='paytoday'>с сегодняшними платежами</option></select>
<input type=submit class=button value='Печать'>
</form><br><br>

<a href='?module=newaccounts&action=bill_mass&obj=create' target=_blank onclick='javascript:return confirm("Точно?")'>Выставить счета всем клиентам за текущий месяц</a><br>
<br>
<a href='?module=newaccounts&action=bill_mass&obj=print'>Печать всех счетов за текущий месяц</a><br>
<br>
<a href='?module=newaccounts&action=bill_balance_mass' target=_blank onclick='javascript:return confirm("Точно?")'>Обновить баланс всем клиентам</a><br>

