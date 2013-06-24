<form method='POST'>
<input type='hidden' name='module' value='tt' />
<input type='hidden' name='action' value='doers' />
<table border="1" cellpadding="3" cellspacing="0" align='center' class='price'>
	<tr style='background-color:lightblue;'><td>Имя</td><td>Активность</td><td>Редактировать/Удалить</td></tr>
	{foreach from=$departs item="depart"}
		<tr align='center' style='background-color:lightgreen;'><td colspan='3'>{$depart}(ы)</td></tr>
		{foreach from=$doers[$depart] item='doer' name='outer'}
			<tr align='center' class="{if $smarty.foreach.outer.iteration%2==count($doers[$depart])%2}even{else}odd{/if}">
				<td>{$doer.name}</td>
				<td>{if $doer.enabled eq 'yes'}Активен{else}Неактивен{/if}</td>
				<td>
					<a href='#' onclick="optools.tt.doer_edit_pane_popup(event);return false;"><img src='images/icons/edit.gif' id='doer_id_{$doer.id}' /></a>
					/
					<a href='?module=tt&action=doers&drop={$doer.id}' onclick="return confirm('Вы действительно хотите удалить\n{$depart}(а) {$doer.name}?');"><img src='images/icons/delete.gif' /></a>
				</td>
			</tr>
		{/foreach}
	{/foreach}
	<tr style='background-color:lightblue;' align='center' ><td colspan='3'>Добавить</td></tr>
	<tr align='center'><td>Имя</td><td>Департамент</td><td><table><tr><td>Активность</td><td>/</td><td>Добавить</td></tr></table></td></tr>
	<tr align='center'>
		<td><input type='text' size='24' name='doer_name' /></td>
		<td><input type='text' size='16' name='doer_depart' onfocus='optools.tt.doers_departs_popup(event);' onblur="optools.tt.doers_departs_popdown(event);" /></td>
		<td>
			<table><tr>
				<td>
					<input type='checkbox' name='doer_active' checked='checked' value='1' />
				</td>
				<td>/</td>
				<td>
					<input type='submit' name='append' value='Добавить' />
				</td>
			</tr></table>
		</td>
	</tr>
</table>
</form>
<table id='deps_store' style='visibility:hidden;background-color:lightgray;position:absolute;'>
{foreach from=$departs item='depart'}
	<tr align='left'><td><a href="#" onclick="optools.tt.doers_buffer.inparea.value='{$depart}';return false;">{$depart}</a></td></tr>
{/foreach}
</table>
{foreach from=$departs item='depart'}{foreach from=$doers[$depart] item='doer'}
<table border="1" cellpadding="3" cellspacing="0" id='doer_edit_pane_{$doer.id}' style='visibility:hidden;background-color:lightgray;position:absolute;'>
<tr align='right'><td><input type='button' value='x' onclick='optools.tt.doer_edit_pane_popdown(event);' /></td></tr>
<tr align='center'><td><b>{$depart} {$doer.name}</b></td></tr>
<tr><td><form method='POST'>
<input type='hidden' name='module' value='tt' />
<input type='hidden' name='action' value='doers' />
<input type='hidden' name='change' value='{$doer.id}' />
<table border="0">
	<tr align='center'><td>Имя</td><td>Департамент</td><td>Активность</td></tr>
	<tr align='center'><td><input type='text' name='doer_name' value='{$doer.name}' /></td><td><input name='doer_depart' type='text' value='{$depart}' size='16' /></td><td><input type='checkbox' name='doer_active' value='1'{if $doer.enabled eq 'yes'}checked='checked'{/if} /></td></tr>
	<tr align='center'><td colspan='3'><input type='submit' value='Обновить' /></td></tr>
</table>
</form></td></tr></table>
{/foreach}{/foreach}