<form method='POST'>
<input type='hidden' name='module' value='tariffs' />
<input type='hidden' name='action' value='tarifs_csv_upload' />
<input type='hidden' name='fix_it' value='1' />
{if isset($error)}
	<h2 style='color:red'>Возникла ошибка! Исправьте ее, или обратитесь к программисту!<br />{$error}</h2>
	{if $repr eq true}
		<h2 style='color:blue'>Данные удалось восстановить</h2>
	{else}
		<h1 style='color:blue'>Данные утеряны!!!</h1>
	{/if}
	{print_r in=$upload_lines}
{/if}
<table border='1' width='100%'>
<tr align='center'>
	<td>Оператор</td>
	<td>Пункт</td>
	<td>DEF</td>
	<td>Диапазон</td>
	<td>Обнаруженые префиксы</td>
	<td>Группа</td>
	<td>Субгруппа</td>
	<td>Цена RUR</td>
	<td>Цена USD</td>
</tr>
{foreach from=$upload_lines item='line' key='lkey'}
	<tr align='center' style='background-color:{if $line.find}gray{else}red{/if}'>
		<td style='padding:0px 0px 0px 0px'>
			<input type='text' value='{$line.operator|escape:input_value_quotes}' style='margin:0px 0px 0px 0px' name='operator[{$lkey}]' />
		</td>
		<td style='padding:0px 0px 0px 0px'>
			<input type='text' value='{$line.region|escape:input_value_quotes}' style='margin:0px 0px 0px 0px' size='8' name='region[{$lkey}]' />
		</td>
		<td style='padding:0px 0px 0px 0px'>
			<input type='hidden' name='def[{$lkey}]' value='{$line.DEF}' />
			{$line.DEF}
		</td>
		<td style='padding:0px 0px 0px 0px'>
			<input type='hidden' name='prefix_from[{$lkey}]' value='{$line.prefixes.from}' />
			<input type='hidden' name='prefix_to[{$lkey}]' value='{$line.prefixes.to}' />
			с {$line.prefixes.from} по {$line.prefixes.to}
		</td>
		<td style='padding:0px 0px 0px 0px'><input type='text' size='30' value='{if $line.find}{implode sep=',' in=$line.prefixes.range}{/if}' name='prefs[{$lkey}]' /></td>
		<td style='padding:0px 0px 0px 0px'><select name='dgroup[{$lkey}]'>
			<option value='0'{if $line.dgroup eq 0} selected='selected'{/if}>Москва (0)</option>
			<option value='1'{if $line.dgroup eq 1} selected='selected'{/if}>Россия (1)</option>
			<option value='2'{if $line.dgroup eq 2} selected='selected'{/if}>Международная (2)</option>
		</select></td>
		<td style='padding:0px 0px 0px 0px'>
			<select name="dsubgroup[{$lkey}]">
				<option value='0'{if $line.dsubgroup==0} selected='selected'{/if}>Мобильные</option>
				<option value='1'{if $line.dsubgroup==1} selected='selected'{/if}>1 Зона/Стационарные</option>
				<option value='2'{if $line.dsubgroup==2} selected='selected'{/if}>2 Зона</option>
				<option value='3'{if $line.dsubgroup==3} selected='selected'{/if}>3 Зона</option>
				<option value='4'{if $line.dsubgroup==4} selected='selected'{/if}>4 Зона</option>
				<option value='5'{if $line.dsubgroup==5} selected='selected'{/if}>5 Зона</option>
				<option value='6'{if $line.dsubgroup==6} selected='selected'{/if}>6 Зона</option>
				<option value='97'{if $line.dsubgroup==7} selected='selected'{/if}>Международное Фрифон</option>
				<option value='98'{if $line.dsubgroup==8} selected='selected'{/if}>Россия Фрифон</option>
				<option value='99'{if $line.dsubgroup==9} selected='selected'{/if}>Другое</option>
			</select>
		</td>
		<td style='padding:0px 0px 0px 0px'><input type='text' value='{$line.price_RUR}' size='4' name='price_RUR[{$lkey}]' /></td>
		<td style='padding:0px 0px 0px 0px'><input type='text' value='{$line.price_USD}' size='4' name='price_USD[{$lkey}]' /></td>
	</tr>
{/foreach}
<tr align='center'><td colspan='9'><input type='submit' value='Go!' /></td></tr>
</table>
</form>