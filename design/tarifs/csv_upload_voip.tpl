<form method='POST' enctype='multipart/form-data'>
<input type='hidden' name='module' value='tarifs' />
<input type='hidden' name='action' value='csv_upload' />
<input type='hidden' name='submit' value='exist' />
<table align='center'>
<tr>
	<td>Группа: </td>
	<td>
		<select name='dgroup'>
			<option value='0' selected='selected'>Москва (0)</option>
			<option value='1'>Россия (1)</option>
			<option value='2'>Международная (2)</option>
		</select>
	</td>
</tr>
<tr>
	<td>Субруппа: </td>
	<td>
		<select name="dsubgroup">
			<option value='0' selected="selected">Мобильные</option>
			<option value='1'>1 Зона/Стационарные</option>
			<option value='2'>2 Зона</option>
			<option value='3'>3 Зона</option>
			<option value='4'>4 Зона</option>
			<option value='5'>5 Зона</option>
			<option value='6'>6 Зона</option>
			<option value='97'>Международное Фрифон</option>
			<option value='98'>Россия Фрифон</option>
			<option value='99'>Другое</option>
		</select>
	</td>
</tr>
<tr>
	<td>Цена RUR: </td>
	<td><input type='text' value='1.08' name='price_RUR' size='5' /></td>
</tr>
<tr>
	<td>Цена USD: </td>
	<td><input type='text' value='0.04' name='price_USD' size='5' /></td>
</tr>
<tr>
	<td>Наценка %:</td>
	<td><input type='text' value='0' name='add_to_price' /></td>
</tr>
<tr>
	<td>Курс USD:</td>
	<td><input type="text" value="0" name="usd_currency" /></td>
</tr>
<tr>
	<td>Формат: </td>
	<td>
		<select name='file_format'>
			<option value='stable_pack'>Тарифы поставщика</option>
			<option value='mtt1'>mtt.ru/Мобильные операторы</option>
		</select>
	</td>
</tr>
<tr>
	<td>Кодировка файла: </td>
	<td>
		<select name='encoding'>
			<option value='cp1251'>WINDOWS-1251</option>
			<option value='koi8r'>KOI8-R</option>
			<option value='utf8'>UTF-8</option>
		</select>
	</td>
</tr>
<tr>
	<td>Файл: </td>
	<td><input type='file' name='csv_file' /></td>
</tr>
<tr align='center'><td colspan='2'><input type='submit' value='Upload' /></td></tr>
</table>
</form>