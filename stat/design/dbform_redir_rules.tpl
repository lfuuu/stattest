<table border=0>
{foreach from=$dbform_f_rules name=dbform_f_inner item=dbform_f_rule}
<form action='?' method=get name=redirrule>
<input type=hidden name=module value=phone><input type=hidden name=action value=tc_edit2><input type=hidden name=id value={$dbform_f_rule.id}>
<tr>
	<td><input type=text name='period' value="{$dbform_f_rule.period}"></td>
	<td><input type=submit class=button value='ok'></td>
</tr>
</form>
{/foreach}
<form action='?' method=get name=redirrule>
<input type=hidden name=module value=phone><input type=hidden name=action value=tc_edit2><input type=hidden name=condition_id value={$dbform_data.id.value}>
<tr>
	<td><input type=text name='period' value=""></td>
	<td><input type=submit class=button value='ok'></td>
</tr>
</form>
</table>