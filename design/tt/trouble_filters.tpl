{if $tt_show_filter}

<div id="trouble_to_filter"{if isset($tt_show_filters) && $tt_show_filters} style="display:none; font-weight: bold; color: #f40000;"{/if}><div onclick="$('#trouble_to_filter').toggle();$('#trouble_filter').toggle();" style="cursor: pointer;"><img border="0" src="./images/icons/add.gif"><u>Фильтр</u></div></div>
<div id="trouble_filter"{if isset($tt_show_filters) && $tt_show_filters} {else} style="display:none;"{/if}>
<div onclick="$('#trouble_filter').toggle();$('#trouble_to_filter').toggle();" style="cursor: pointer;"><img border="0" src="./images/icons/add.gif"><u>Фильтр (спрятать)</u></div>
<form method='POST' action="./">
	<input type='hidden' name='module' value='tt' />
	<input type='hidden' name='action' value='{$filter_head.action}' />
	<input type='hidden' name='mode' value='{$filter_head.mode}' />
	<input type='hidden' name='type_pk' value='{$tt_type.pk}' />
	<input type='hidden' name='filters_flag' value='true'/>
	<input type='hidden' name='filter_set' value='true'/>
	<input type='hidden' name='isnew' value='{if $isNewView}true{else}false{/if}'/>

	<table>
		<tr align='center'><td></td><td></td><td rowspan='7'><input type='submit' value='Выбрать' /><input type='submit' name="cancel" value='Снять фильтр' /></td></tr>
		<tr>
			<td>Дата создания </td>
			<td id="td_is_create"><input type=checkbox id=is_create name=is_create onclick="{literal}var isCreate=$('#is_create')[0].checked; $('#td_is_create select').each(function(o,i){i.disabled = !isCreate;});{/literal}" {if $is_create} checked{/if}> С
				<select name='date_from_y'{if !$is_create} disabled{/if}><option value='---'>--</option>{generate_sequence_options_select mode='Y' start='2003' selected=$date_from_y}</select>
				<select name='date_from_m'{if !$is_create} disabled{/if}>{generate_sequence_options_select mode='m' start='1' end='12' selected=$date_from_m}</select>
				<select name='date_from_d'{if !$is_create} disabled{/if}>{generate_sequence_options_select mode='d' start='1' end='31' selected=$date_from_d}</select>
				по
				<select name='date_to_y'{if !$is_create} disabled{/if}><option value='---'>--</option>{generate_sequence_options_select mode='Y' start='2003' selected=$date_to_y}</select>
				<select name='date_to_m'{if !$is_create} disabled{/if}>{generate_sequence_options_select mode='m' start='1' end='12' selected=$date_to_m}</select>
				<select name='date_to_d'{if !$is_create} disabled{/if}>{generate_sequence_options_select mode='d' start='1' end='31' selected=$date_to_d}</select>
			</td>
		</tr>
		<tr>
			<td>Дата начала тек.этапа</td>
			<td id="td_is_active"><input type=checkbox id=is_active name=is_active onclick="{literal}var isActive=$('#is_active')[0].checked; $('#td_is_active select').each(function(o,i){i.disabled = !isActive;});{/literal}" {if $is_active} checked{/if}>С
				<select name='date_active_from_y'{if !$is_active} disabled{/if}><option value='---'>--</option>{generate_sequence_options_select mode='Y' start='2003' selected=$date_active_from_y}</select>
				<select name='date_active_from_m'{if !$is_active} disabled{/if}>{generate_sequence_options_select mode='m' start='1' end='12' selected=$date_active_from_m}</select>
				<select name='date_active_from_d'{if !$is_active} disabled{/if}>{generate_sequence_options_select mode='d' start='1' end='31' selected=$date_active_from_d}</select>
				по
				<select name='date_active_to_y'{if !$is_active} disabled{/if}><option value='---'>--</option>{generate_sequence_options_select mode='Y' start='2003' selected=$date_active_to_y}</select>
				<select name='date_active_to_m'{if !$is_active} disabled{/if}>{generate_sequence_options_select mode='m' start='1' end='12' selected=$date_active_to_m}</select>
				<select name='date_active_to_d'{if !$is_active} disabled{/if}>{generate_sequence_options_select mode='d' start='1' end='31' selected=$date_active_to_d}</select>
			</td>
		</tr>
		<tr>
			<td>Дата закрытия</td>
			<td id="td_is_close"><input type=checkbox id=is_close name=is_close onclick="{literal}var isClose=$('#is_close')[0].checked; $('#td_is_close select').each(function(o,i){i.disabled = !isClose;});{/literal}" {if $is_close} checked{/if}>С
				<select name='date_close_from_y'{if !$is_close} disabled{/if}><option value='---'>--</option>{generate_sequence_options_select mode='Y' start='2003' selected=$date_close_from_y}</select>
				<select name='date_close_from_m'{if !$is_close} disabled{/if}>{generate_sequence_options_select mode='m' start='1' end='12' selected=$date_close_from_m}</select>
				<select name='date_close_from_d'{if !$is_close} disabled{/if}>{generate_sequence_options_select mode='d' start='1' end='31' selected=$date_close_from_d}</select>
				по
				<select name='date_close_to_y'{if !$is_close} disabled{/if}><option value='---'>--</option>{generate_sequence_options_select mode='Y' start='2003' selected=$date_close_to_y}</select>
				<select name='date_close_to_m'{if !$is_close} disabled{/if}>{generate_sequence_options_select mode='m' start='1' end='12' selected=$date_close_to_m}</select>
				<select name='date_close_to_d'{if !$is_close} disabled{/if}>{generate_sequence_options_select mode='d' start='1' end='31' selected=$date_close_to_d}</select>
			</td>
		</tr>
		<tr>
			<td>Создатель заявки</td>
			<td>
				<select name='owner'>
					<option value='---'>Все</option>
                    {html_options options=$owners selected=$filter.owner}
				</select>
			</td>
		</tr>
		<tr>
			<td>Ответственный</td>
			<td>
				<select name='resp'>
					<option value='---'>Все</option>
					<option value='SUPPORT' {if $filter.resp == "SUPPORT"} selected{/if}>SUPPORT</option>
                    {html_options options=$resps selected=$filter.resp}
				</select>
			</td>
		</tr>
		<tr>
			<td>Редактирующий</td>
			<td>
				<select name='edit'>
					<option value='---'>Все</option>
                    {html_options options=$editors selected=$filter.edit}
				</select>
			</td>
		</tr>
		<tr>
			<td>Тип заявки</td>
			<td>
				<select name='subtype'>
					<option value='---'>Все</option>
                    {html_options options=$trouble_subtypes selected=$filter.subtype}
				</select>
			</td>
		</tr>
{if false}
		<tr>
			<td>Клиент</td>
			<td>
				<select name='client'>
					<option value='---'>Все</option>{foreach from=$clients item='client'}
					<option value='{$client.client}'>{$client.client}</option>
					{/foreach}
				</select>
			</td>
		</tr>
{/if}
	</table>
</form>
</div>
{/if}
