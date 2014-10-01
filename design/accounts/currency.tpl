<h2>Ввод курса доллара</h2>

<i>{$message}</i><br>

<form action="?module=accounts&action=accounts_add_usd_rate&todo=add_rate" method="POST">
<table align="center">
	<tr>
		<TD>Курс доллара</TD><td><INPUT type="text" name="rate" value="00.0000"></td>
		<TD>Дата</TD><td><INPUT type="text" name="date" value="ГГГГ-ММ-ДД"></td>
		<TD><INPUT type="submit" name="payment" value="Внести курс"></TD>
	</tr>

</table>
</form>
