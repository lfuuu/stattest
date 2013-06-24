<form action='?' method='GET' name='tarifs_date'>
<input type='hidden' name='module' value='stats' />
<input type='hidden' name='action' value='voip_sell' />
<table align='center' style='background-color:silver;' border='0'>
	<tr><td></td><td></td><td></td><td></td><td rowspan='4' style='vertical-align:top;padding-top:6px;'>
		<input type='checkbox' name='choice[]' value='tarifs' checked='checked' /> По тарифам
	</td></tr>
	<tr style='text-align:center;background-color:silver;'><td colspan='4' id='stat_voip_sell_subbutton'><input type='submit' value='Выбрать' /></td></tr>
	<script type='text/javascript'>
		document.getElementById('stat_voip_sell_subbutton').innerHTML = "<a href='#' onclick='document.forms.tarifs_date.submit();'>Выбрать</a>"
	</script>
	<tr><td>С </td><td><select name='date_from_y' onchange='optools.friendly.dates.check_mon_right_days_count(this,"date_from_m","date_from_d")'>{generate_sequence_options_select start='2003' selected=$date_from_y}</select></td><td><select name='date_from_m' onchange='optools.friendly.dates.check_mon_right_days_count("date_from_y",this,"date_from_d")'>{generate_sequence_options_select start='1' end='12' mode='m' selected=$date_from_m}</select></td><td><select name='date_from_d'>{generate_sequence_options_select start='1' end='31' mode='d' selected=$date_from_d}</select></td></tr>
	<tr><td>По</td><td><select name='date_to_y' onchange='optools.friendly.dates.check_mon_right_days_count(this,"date_to_m","date_to_d")'>{generate_sequence_options_select start='2003' selected=$date_to_y}</select></td><td><select name='date_to_m' onchange='optools.friendly.dates.check_mon_right_days_count("date_to_y",this,"date_to_d")'>{generate_sequence_options_select start='1' end='12' mode='m' selected=$date_to_m}</select></td><td><select name='date_to_d'>{generate_sequence_options_select start='1' end='31' mode='d' selected=$date_to_d}</select></td></tr>
</table>
</form>
<script type='text/javascript'>
	optools.friendly.dates.check_mon_right_days_count("date_from_y","date_from_m","date_from_d")
	optools.friendly.dates.check_mon_right_days_count("date_to_y","date_to_m","date_to_d")
</script>
{if in_array('tarifs',$choice)}{*Если выбрали по тарифам, выдаем статистику по тарифам*}
<table id='stats_tarifs_stats' style='display:none;position:absolute;background-color:aqua;'>
<tr align='right'><td><a href='#' onclick='document.getElementById("stats_tarifs_stats").style.display="none";return false;'>Закрыть</a></td></tr>
<tr><td id='stats_tarifs_stats_field'></td></tr>
</table>
<script type='text/javascript'>{literal}
	if(!optools)
		optools = {}
	if(!optools.stats)
		optools.stats = {}
	if(!optools.stats.voip_sell)
		optools.stats.voip_sell = {}
	optools.stats.voip_sell.displayBillsTable = function(evt,bills){
		var txt='',b = bills.split(',')
		for(i in b){
			txt += '<a href="?module=newaccounts&action=bill_view&bill='+b[i]+'" target="_blank" style="text-decoration:none">'+b[i]+'</a>'
			if(i<(b.length-1))
				txt += '<br />'
		}
		document.getElementById('stats_tarifs_stats_field').innerHTML = txt
		pos = optools.getFullOffset(optools.getEventSource(evt))
		var tbl = document.getElementById('stats_tarifs_stats')
		tbl.style.display = 'block'
		tbl.style.top = pos.y + optools.getEventSource(evt).offsetHeight + 2
		tbl.style.left = pos.x - tbl.offsetWidth/3
		return false
	}
{/literal}</script>
<table width='98%' border='0'>
<tr style='text-align:center;background-color:#aaaaaa;color:#222222;font-weight:bolder;'><td width='20%'>Клиент</td><td width='50%'>Тарифный план</td><td width='15%'>RUR</td><td width='15%'>USD</td></tr>
<tr style='text-align:center;color:black;background-color:#aaaaaa;'><td colspan='2' style='text-align:right;padding-right:100px'>Общая сумма:</td><td>{$tarifs_stats.totals.rur}</td><td>{$tarifs_stats.totals.usd}</td></tr>
{foreach from=$tarifs_stats.rows item='row' key='k'}
	<tr style='background-color:{if $k is odd}silver{else}#dddddd{/if};'><td><a href='?module=clients&id={$row.client_id}' style='text-decoration:none' target='_blank'>{$row.client}</a></td><td><a href='?module=tarifs&action=edit&m=voip&id={$row.tarif_id}' style='text-decoration:none' target='_blank'>{$row.tarif}</a></td><td>{if $row.currency eq 'RUR'}<a href='#' onclick='return optools.stats.voip_sell.displayBillsTable(event,"{$row.bills}")' style='text-decoration:none'>{$row.total}</a>{else}&nbsp;{/if}</td><td>{if $row.currency eq 'USD'}<a href='#' onclick='return optools.stats.voip_sell.displayBillsTable(event,"{$row.bills}")' style='text-decoration:none'>{$row.total}</a>{else}&nbsp;{/if}</td></tr>
{/foreach}
</table>
{/if}