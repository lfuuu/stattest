<h2>Статистика по IP-адресу {$ip}</h2>
{if !$period}
<h3>Сегодня:</h3>
<img src='img_stat.php?ip={$ip}&period=1'>

<h3>Вчера</h3>
<img src='img_stat.php?ip={$ip}&period=2'>

<h3>Текущий месяц</h3>
<img src='img_stat.php?ip={$ip}&period=3'>

<h3>Предыдущий месяц</h3>
<img src='img_stat.php?ip={$ip}&period=4'>
{elseif $period=='day'}
<h2>{$curdate|mdate:'d месяца Y'}</h2>
<img src='img_stat.php?ip={$ip}&period=day&y={$y}&m={$m}&d={$d}'>
{elseif $period=='month'}
<h2>{$curdate|mdate:'Месяц Y'}</h2>
<img src='img_stat.php?ip={$ip}&period=month&y={$y}&m={$m}'>
{/if}

<h3>Дополнительная статистика</h3>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<form action='?' method=get>
<input type=hidden name=module value=monitoring>
<TR><TD class=left>IP-адрес:</TD><TD><input class=text type=text name=ip value="{$ip}"></TD></TR>
<TR><TD class=left>Период:</TD><TD>
	<select name=period class=text>
		<option value=day>сутки</option>
		<option value=month>месяц</option>
	</select>
</TD></TR>
<TR><TD class=left>Дата:</TD><TD>
	<select name=d class=text>{foreach from=$D item=di}
		<option value={$di}{if $d==$di} selected{/if}>{$di}</option>
	{/foreach}</select>
	<select name=m class=text>
		<option value="01"{if $m=="01"} selected{/if}>январь</option>
		<option value="02"{if $m=="02"} selected{/if}>февраль</option>
		<option value="03"{if $m=="03"} selected{/if}>март</option>
		<option value="04"{if $m=="04"} selected{/if}>апрель</option>
		<option value="05"{if $m=="05"} selected{/if}>май</option>
		<option value="06"{if $m=="06"} selected{/if}>июнь</option>
		<option value="07"{if $m=="07"} selected{/if}>июль</option>
		<option value="08"{if $m=="08"} selected{/if}>август</option>
		<option value="09"{if $m=="09"} selected{/if}>сентябрь</option>
		<option value="10"{if $m=="10"} selected{/if}>октябрь</option>
		<option value="11"{if $m=="11"} selected{/if}>ноябрь</option>
		<option value="12"{if $m=="12"} selected{/if}>декабрь</option>
	</select>
	<select name=y class=text>
		{generate_sequence_options_select start='2006' end=$smarty.now|date_format:"%Y" selected=$y}
		{*<option{if $y==2006} selected{/if}>2006</option>
		<option{if $y==2007} selected{/if}>2007</option>
		<option{if $y==2008} selected{/if}>2008</option>*}
	</select> (если период=месяц, то "число" можно ставить любое)
</TD></TR>
<TR><TD>&nbsp;</TD><TD><input class=button type=submit value='Показать'></td></tr>
</form>
</table>

<h3>Пояснения</h3>
На рисунке показана завимость цвета от процента потерь:<br>
<img src='img_stat.php'>

<h3>Общие данные</h3>
monitor_5min: {$data1.C*100|round:4}%; min {$data1.A|mdate:'Y-m-d H:i:d'}, max {$data1.B|mdate:'Y-m-d H:i:d'}<br>
monitor_1h: {$data2.C*100|round:4}%; min {$data2.A|mdate:'Y-m-d H:i:d'}, max {$data2.B|mdate:'Y-m-d H:i:d'}<br>
<br><br>
