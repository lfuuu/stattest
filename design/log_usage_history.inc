
<div id='log_usage_history'>
	<table cellpadding=4 cellspasing=4>
		<tr><td>Дата/время</td><td>Пользователь</td><td>Изменение</td></tr>
		{foreach from=$dbform_log_usage_history item='log_rec'}
			<tr>
				<td><b>{$log_rec.ts}</b></td>
				<td><b>{$log_rec.user}</b></td>
				<td>{foreach from=$log_rec.fields item='r' key='field'}<b>{$r.name}</b>: {$r.value_from} => {$r.value_to}<br />{/foreach}</td>
			</tr>
		{/foreach}
	</table>
</div>
