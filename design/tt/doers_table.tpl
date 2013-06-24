<table class="price" id="doers_table" align='center' {if $show eq false}style="display:none;"{/if}>
<tr bgcolor='lightblue' style="margin-bottom:2px;">
	<td class="header" align='center'>Исполнитель</td>
	<td class="header" align='center'>Департамент</td>
	<td class="header" align='center'>?</td>
</tr>{foreach from=$doers item="doer" name="outer"}
<tr class="{if $smarty.foreach.outer.iteration%2==count($tt_trouble.stages)%2}even{else}odd{/if}" style="margin-bottom:2px;">
	<td align='center'>{$doer.name}</td>
	<td align='center'>{$doer.depart}</td>
	<td align='center'><input type='checkbox' name='doer[{$doer.id}]' value='1'{if $doer.checked eq 'Y'}checked="checked"{/if} /></td>
</tr>
{/foreach}
<tr class="even">
	<td colspan="3" align='center'>
		<a href="#" onclick="document.getElementById('oops_button_pane').style.visibility='visible'; return false;">Ошибка?</a>
	</td>
</tr>
<tr bgcolor='gray' id="oops_button_pane" style="visibility:hidden">
	<td colspan="3" align='center'>
		<input type='button' value='Ooops...' onclick="return optools.tt.refix_doers(this)" />
	</td>
</tr>
</table>