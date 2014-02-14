<h2>{$name_of_action}</h2>
Найдено: {$cl_pager_all} клиентов<br>
{if count($cl_pager_pages)>1}
Страницы: 
{foreach from=$cl_pager_pages item=i} {if $cl_pager_page == $i} {$i} {else} <a href='{$cl_pager_url}&page={$i}'>{$i}</a>{/if} {/foreach}<br>
{/if}

<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>

	<TD class=header vAlign=bottom width="15%">{sort_link sort=1 text='Клиент' link='?module=clients&action=' link2=$action link3='&subj=' link4=$client_subj link5='&search=' link6=$search link7='&letter=' link8=$letter sort_cur=$sort so_cur=$so}</TD>
	<TD class=header vAlign=bottom width="25%">{sort_link sort=2 text='Компания' link='?module=clients&action=' link2=$action link3='&subj=' link4=$client_subj link5='&search=' link6=$search link7='&letter=' link8=$letter sort_cur=$sort so_cur=$so}</TD>
	<TD class=header vAlign=bottom width="20%">{sort_link sort=8 text='Дата поступления' link='?module=clients&action=' link2=$action link3='&subj=' link4=$client_subj link5='&search=' link6=$search link7='&letter=' link8=$letter sort_cur=$sort so_cur=$so}</TD>
	<TD class=header vAlign=bottom width="15%">{sort_link sort=3 text='Валюта' link='?module=clients&action=' link2=$action link3='&subj=' link4=$client_subj link5='&search=' link6=$search link7='&letter=' link8=$letter sort_cur=$sort so_cur=$so}</TD>
	<TD class=header vAlign=bottom width="15%">{sort_link sort=4 text='Канал' link='?module=clients&action=' link2=$action link3='&subj=' link4=$client_subj link5='&search=' link6=$search link7='&letter=' link8=$letter sort_cur=$sort so_cur=$so}</TD>
	<TD class=header vAlign=bottom width="10%">{sort_link sort=5 text='SD' link='?module=clients&action=' link2=$action link3='&subj=' link4=$client_subj link5='&search=' link6=$search link7='&letter=' link8=$letter sort_cur=$sort so_cur=$so}</TD>
	<TD class=header vAlign=bottom width="10%">{sort_link sort=6 text='TP' link='?module=clients&action=' link2=$action link3='&subj=' link4=$client_subj link5='&search=' link6=$search link7='&letter=' link8=$letter sort_cur=$sort so_cur=$so}</TD>
	<TD class=header vAlign=bottom width="10%">{sort_link sort=7 text='TM' link='?module=clients&action=' link2=$action link3='&subj=' link4=$client_subj link5='&search=' link6=$search link7='&letter=' link8=$letter sort_cur=$sort so_cur=$so}</TD>

</TR>
{foreach from=$clients item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==count($clients)%2}even{else}odd{/if}>
	<TD {if $item.status_color}style='background-color:{$item.status_color}'{/if}><a href='{$LINK_START}module=clients&id={$item.id}'>{if $item.client==""}Заявка {$item.id|hl:$search}{else}{$item.client|hl:$search}{/if}</a></TD>
	<TD><a href='{$LINK_START}module=clients&id={$item.id}'>{$item.company|hl:$search}</a></TD>
	<TD>{$item.created}</TD>
	<TD>{$item.currency}</TD>
	<TD>{$item.sale_channel}</TD>
	<TD><a href='{$LINK_START}module=users&m=user&id={$item.manager}'>{$item.manager|hl:$search}</a></TD>
	<TD><a href='{$LINK_START}module=users&m=user&id={$item.support}'>{$item.support|hl:$search}</a></TD>
	<TD><a href='{$LINK_START}module=users&m=user&id={$item.support}'>{$item.telemarketing|hl:$search}</a></TD>
</TR>
{/foreach}
<tr><td colspan='8'>Фильтры:<br>
	<form method='POST'>
	<table>
		<tr>
			<td>C</td>
			<td><select name='filter_clients_date_from_y'>{generate_sequence_options_select start='2003' mode='Y' selected=$filter_clients_date_from_y}</select></td>
			<td><select name='filter_clients_date_from_m'>{generate_sequence_options_select start='1' end='12' mode='m' selected=$filter_clients_date_from_m}</select></td>
			<td><select name='filter_clients_date_from_d'>{generate_sequence_options_select start='1' end='31' mode='d' selected=$filter_clients_date_from_d}</select></td>
			<td>по</td>
			<td><select name='filter_clients_date_to_y'>{generate_sequence_options_select start='2003' mode='Y' selected=$fitlter_clients_date_to_y}</select></td>
			<td><select name='filter_clients_date_to_m'>{generate_sequence_options_select start='1' end='12' mode='m' selected=$filter_clients_date_to_m}</select></td>
			<td><select name='filter_clients_date_to_d'>{generate_sequence_options_select start='1' end='31' mode='d' selected=$filter_clients_date_to_d}</select></td>
			<td><input type='submit' value='Выбрать' /></td>
		</tr>
	</table>
	</form>
</td></tr>
</TBODY></TABLE>
