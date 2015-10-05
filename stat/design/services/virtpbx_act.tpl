<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>АКТ &#8470;3-{$client.id}</title>
</head>
<body>
	<style>
<!--
BODY { "
	font-size: 9px;
	"
}
-->
</style>

		<p align="CENTER"
			style="margin-top: 0.05cm; margin-bottom: 0.05cm; line-height: 100%">
			<font face="Times New Roman, serif"><font size="5"><b>АКТ &#8470;3-{$client.id}</b>
			</font>
			</font>
		</p>
		<p align="CENTER"
			style="margin-top: 0.05cm; margin-bottom: 0.05cm; line-height: 100%">
			<font face="Times New Roman, serif"><font size="4"
				style="font-size: 13pt"><b>сдачи-приемки работ</b>
			</font>
			</font>
		</p>
			<table width=100% border="0" cellpadding="1" cellspacing="0">
				<tbody>
					<tr>
						<td width="177">
							<p>
								<font face="Times New Roman, serif">г. Москва </font>
							</p></td>
						<td width="470">
							<p align="RIGHT">
								<font face="Times New Roman, serif">{$d.actual_from|mdate:"d месяца Y"} г.</font>
							</p></td>
					</tr>
				</tbody>
			</table>
		<p
			style="margin-top: 0.05cm; margin-bottom: 0.05cm; line-height: 100%">
			<font face="Times New Roman, serif">Настоящий акт составлен
				между Абонентом </font><font face="Times New Roman, serif"><font
				size="3"><b>{$client.company_full}</b>
			</font>
			</font><font face="Times New Roman, serif">, в&nbsp;лице </font><font
				face="Times New Roman, serif"><font size="3"><b>{$client.signer_positionV} {$client.signer_nameV}</b>
			</font>
			</font><font face="Times New Roman, serif">, и&nbsp;Оператором ООО
				&laquo;МСН Телеком&raquo;, в&nbsp;лице {$firm_director.position_}
				{$firm_director.name_}, о&nbsp; том, что услуга &laquo;Виртуальная
				АТС&raquo; подключена.</font>
		</p>
		<p
			style="margin-top: 0.05cm; margin-bottom: 0.05cm; line-height: 100%">
			<font face="Times New Roman, serif">Данные для доступа к
				услуге переданы представителю Абонента, услуга функционируют
				нормально и&nbsp;удовлетворяет требованиям Договора.</font>
		</p>
		<p style="margin-bottom: 0cm; line-height: 100%">
			<font face="Times New Roman, serif"><font size="3"><b>Доступ
						к услуге:</b>
			</font>
			</font><font face="Times New Roman, serif"> </font><font
				face="Times New Roman, serif"><font size="3"><i>https://lk.mcn.ru</i>
			</font>
			</font><font face="Times New Roman, serif"> <br />Логин: </font><font
				face="Times New Roman, serif"><font size="3"><b>{$client.client}</b>
			</font>
			</font><font face="Times New Roman, serif"><br />Пароль: </font><font
				face="Times New Roman, serif"><font size="3"><b>{$client.password}</b>
			</font>
			</font>
		</p>
		<p style="margin-bottom: 0cm; line-height: 100%">
			<br />
		</p>
		<p style="margin-bottom: 0cm; line-height: 100%">
			<br />
		</p>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<colgroup>
				<col width="50%">
				<col width="50%">
			</colgroup>
			<tbody>
				<tr>
					<td>
						<p style="margin-top: 0.05cm">
							<font face="Times New Roman, serif">Оператор: ООО
								&laquo;МСН Телеком&raquo;</font>
						</p></td>
					<td>
						<p>
							<font face="Times New Roman, serif">Абонент: </font><font
								face="Times New Roman, serif"><font size="3"><b>{$client.company_full}</b>
							</font>
							</font><font face="Times New Roman, serif"> </font>
						</p></td>
				</tr>
				<tr>
					<td>
						<p>
							<font face="Times New Roman, serif"><br />
                                {$firm_director.position} ___________ / {$firm_director.name} /
                            </font>
						</p></td>
					<td>
						<p style="margin-bottom: 0cm">
							<font face="Times New Roman, serif"><br />{$client.signer_position} _____________/ {$client.signer_name}/ </font>
						</p></td>
				</tr>
			</tbody>
		</table>

</body>
</html>
