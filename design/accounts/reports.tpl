<h1>Бухгалтерские отчеты</h1>

<form action="?module=accounts&action=accounts_reports" method="POST">
<table align="center" border="1">
<tr>
	<TD><INPUT type="radio" name="report" value="sale_book">книга продаж</td>
	<td><INPUT type="radio" name="report" value="services_month">отчет по услугам за месяц</TD>
	<td><INPUT type="radio" name="report" value="services_quartal">отчет по услугам за период</TD>
	<TD><INPUT type="radio" name="report" value="report_month">отчет за месяц(новый)</TD>
	
</tr>
<tr>
<td>Компания:</td><td>
<SELECT name="firma">
  <option value="mcn">ООО MCN</option>
  <option value="markomnet">МАРКОМНЕТ</option>
  <option value="all">Все</option>
</SELECT>
</td>
</tr>
<tr>
<td>Период</td>
	<TD><INPUT type="text" name="period" value="2005-01" size="10">
	</TD>
	<TD><INPUT type="text" name="period_to" value="2005-03" size="10">
	</TD>
</tr>
<tr>
	<TD>Проводные <INPUT type="radio" name="provod" value="pr"></td>
	<td>Все<INPUT type="radio" name="provod" value="all"></TD>
</tr>

<tr>
<TD colspan="2"><INPUT type="submit" value="Сформировать отчет">
</TD>
</tr>

</form>
</table>