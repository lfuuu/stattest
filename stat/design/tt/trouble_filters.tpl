{if $tt_show_filter}

<div id="trouble_to_filter"{if isset($tt_show_filters) && $tt_show_filters} style="display:none; font-weight: bold; color: #f40000;"{/if}><div onclick="$('#trouble_to_filter').toggle();$('#trouble_filter').toggle();" style="cursor: pointer;"><img border="0" src="./images/icons/add.gif"><u>Фильтр</u></div></div>
<div id="trouble_filter"{if isset($tt_show_filters) && $tt_show_filters} {else} style="display:none;"{/if}>
<div onclick="$('#trouble_filter').toggle();$('#trouble_to_filter').toggle();" style="cursor: pointer;"><img border="0" src="./images/icons/add.gif"><u>Фильтр (спрятать)</u></div>
<form method='POST' action="./">
	<input type='hidden' name='module' value='tt' />
	<input type='hidden' name='action' value='{$filter_head.action}' />
	<input type='hidden' name='mode' value='{$filter_head.mode}' />
	<input type='hidden' name='type_pk' value='{if isset($tt_type)}{$tt_type.pk}{/if}' />
	<input type='hidden' name='filters_flag' value='true'/>
	<input type='hidden' name='filter_set' value='true'/>

	<table>
		<tr align='center'><td></td><td></td><td rowspan='7'><input type='submit' value='Выбрать' /><input type='submit' name="cancel" value='Снять фильтр' /></td></tr>
		<tr>
			<td>Дата создания </td>
			<td id="td_is_create">
				<input type=checkbox id=is_create name=is_create onclick="{literal}var isCreate=$('#is_create')[0].checked; $('#td_is_create input[type=text]').each(function(o,i){i.disabled = !isCreate;});{/literal}" {if $is_create} checked{/if}> 
				С <input class="datepicker-input" type=text {if !$is_create}disabled {/if}name="create_date_from" value="{$create_date_from}" id="create_date_from">
				По <input class="datepicker-input" {if !$is_create}disabled {/if}type=text name="create_date_to" value="{$create_date_to}" id="create_date_to">
			</td>
		</tr>
		<tr>
			<td>Дата начала тек.этапа</td>
			<td id="td_is_active">
				<input type=checkbox id=is_active name=is_active onclick="{literal}var isActive=$('#is_active')[0].checked; $('#td_is_active input[type=text]').each(function(o,i){i.disabled = !isActive;});{/literal}" {if $is_active} checked{/if}>
				С <input class="datepicker-input" type=text {if !$is_active}disabled {/if}name="active_date_from" value="{$active_date_from}" id="active_date_from">
				По <input class="datepicker-input" {if !$is_active}disabled {/if}type=text name="active_date_to" value="{$active_date_to}" id="active_date_to">
			</td>
		</tr>
		<tr>
			<td>Дата закрытия</td>
			<td id="td_is_close">
				<input type=checkbox id=is_close name=is_close onclick="{literal}var isClose=$('#is_close')[0].checked; $('#td_is_close input[type=text]').each(function(o,i){i.disabled = !isClose;});{/literal}" {if $is_close} checked{/if}>
				С <input class="datepicker-input" type=text {if !$is_close}disabled {/if}name="close_date_from" value="{$close_date_from}" id="close_date_from">
				По <input class="datepicker-input" {if !$is_close}disabled {/if}type=text name="close_date_to" value="{$close_date_to}" id="close_date_to">
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
<script>
optools.DatePickerInit('create_');
optools.DatePickerInit('active_');
optools.DatePickerInit('close_');
</script>
{/if}
