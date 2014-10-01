<h3>IP-телефония</h3>
<FORM action="?" method=post id=dbform name=dbform>
<input type=hidden name=module value=phone>
<input type=hidden name=action value=voip_edit>
<input type=hidden name=id value={$r.id}>

<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR><TD class=left width=40%>Подключение активно с</TD><TD>{$r.actual_from}</TD></TR>
{if $voip_access}
	<TR><TD class=left width=40%>Подключение активно по</TD><TD><input type=text name=f[actual_to] value="{$r.actual_to}"></TD></TR>
{else}
	<TR><TD class=left width=40%>Подключение активно по</TD><TD>{$r.actual_to}</TD></TR>
{/if}
<TR><TD class=left width=40%>Назначенный номер</TD><TD>{$r.E164}</TD></TR>
{if $r.tarif}<TR><TD class=left width=40%>Текущий тариф</TD><TD>{$r.tarif.name}</TD></TR>{/if}
{if $voip_access}
	<TR><TD class=left width=40%>
	{if !$r.tarif}Тариф{elseif $r.tarif_tomorrow}Тариф, который будет с завтрашнего дня{else}Сменить тариф на{/if}
	<TD><select name=f[new_tarif_id]>
	{if !$r.tarif_tomorrow && $r.tarif}
		<option value=0>(не менять)</option>
	{/if}
	{foreach from=$voip_tarifs item=i}
		<option value={$i.id}{if isset($r.tarif_tomorrow) && $r.tarif_tomorrow.id==$i.id} selected{/if}>{$i.name}</option>
	{/foreach}</select></TD></TR>
{/if}

<TR><TD class=left width=40%>Логин</TD><TD>{$secret.username}</TD></TR>
<TR><TD class=left width=40%>Пароль</TD><TD>{$secret.secret}</TD></TR>

{*<TR><TD class=left width=40%>Количество линий</TD><TD><input type=text name=f[no_of_lines] value="{$r.no_of_lines}"></TD></TR>*}
</TBODY></TABLE>
{if !$r.id}
<DIV align=center><INPUT id=submit class=button type=submit value="Добавить"></DIV>
{else}
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>
{/if}
</form>
