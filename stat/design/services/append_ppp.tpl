<form method='POST'>
<input type='hidden' name='append_ppp_ok' value='1' />
<table align='center' style='text-align:center'>
	<tr>
		<td>Клиент</td>
		<td>{$ass.client}</td>
	</tr>
	<tr>
		<td>Новый PPPOE логин</td>
		<td><input type='text' name='pppoe_login' value='{$ass.login}' /></td>
	</tr>
	<tr>
		<td>Пароль</td>
		<td><input type='text' name='pppoe_pass' value='{$ass.password}' /></td>
	</tr>
	<tr>
		<td>IP адрес</td>
		<td><input type='text' name='ip_address' value='{$ass.ip}' /></td>
	</tr>
	<tr>
		<td>NAT to IP</td>
		<td><input type='text' name='nat_2_ip' value='{$ass.nat_2_ip}' /></td>
	</tr>
	<tr><td colspan='2'><input type='submit' value='Добавить' /></td></tr>
</table>
</form>