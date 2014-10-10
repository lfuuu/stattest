<a href='?module=monitoring&action=edit&dbform_action=delete&dbform[id]={$dbform_data.id.value}'>Удалить</a><br>
<h3>Текущие IP-адреса с ошибками:</h3>
<table class=price border=0 width=100%>{foreach from=$dbform_f_monitoring item=T key=key name=monitoring_1}
<tr class=even><td>
	{ipstat net=$T.ip}{$T.count} плох{$T.count|rus_fin:'ой':'их':'их'} пинг{$T.count|rus_fin:'':'а':'ов'}
</td><td>
	<img src='img_stat.php?ip={$T.ip}&period=1'>
</tr>
{/foreach}
</table>