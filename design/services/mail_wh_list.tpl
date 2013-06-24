<H2>Услуги</H2>
Настройка списка адресов, письма с которых Вы не считаете спамом<br>
<H3>Список {if $em_filter} для адреса {$em_filter.local_part}@{$em_filter.domain} <span style='font-size:75%'>(<a href='{$LINK_START}module=services&action=em_whitelist'>тонкая настройка белого списка</a>)</span>{/if}</h3>
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<TR>
	<TD class=header vAlign=bottom width="40%" colspan=3>ваш адрес</TD>
	<TD class=header vAlign=bottom width="40%" colspan=3>адрес отправителя</TD>
	<td width=20%>&nbsp;</td>
</TR>
{foreach from=$whlist item=item name=outer}
<tr class="{cycle values="even,odd"}"> 
{if $item.local_part}
	<td align=right>{$item.local_part}</td>
{else}
	<td align=right><b>*</b></td>
{/if}
	<td align=center style='padding:0 0 0 0;margin:0 0 0 0' width=1%>@</td>
	<td>{$item.domain}</td>

{if $item.sender_address}
	<td align=right>{$item.sender_address|regex_replace:"/^(.+)@(.+)$/":"\\1"}</td>
	<td align=center style='padding:0 0 0 0;margin:0 0 0 0' width=1%>@</td>
	<td>{$item.sender_address|regex_replace:"/^(.+)@(.+)$/":"\\2"}</td>
{elseif $item.sender_address_domain}
	<td align=right><b>*</b></td>
	<td align=center style='padding:0 0 0 0;margin:0 0 0 0' width=1%>@</td>
	<td>{$item.sender_address_domain}</td>
{else}
	<td align=right><b>*</b></td>
	<td align=center style='padding:0 0 0 0;margin:0 0 0 0' width=1%>@</td>
	<td><b>*</b></td>
{/if}
	<td>
		<a href="{$LINK_START}module=services&action=em_whitelist_delete&filter={$em_filter.id}&id={$item.id}" onclick='javascript:return(confirm("Вы уверены?"))'>удалить</a>
		&nbsp;</td>
</tr>
{/foreach}
</tbody>
</table>
<br><br><h3>Добавить адрес</h3>
<FORM action="?" method=post name=form id=form>
<input type=hidden name=action value=em_whitelist_add>
<input type=hidden name=module value=services>
<input type=hidden name=filter value={$em_filter.id}>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR><TD class=left>
	Ваш адрес:
</TD><TD>
{if $em_filter}
	<input name=adr_radio0 type=hidden value=0>
	<input name=mail0 type=hidden value="{$em_filter.local_part}@{$em_filter.domain}">
	{$em_filter.local_part}@{$em_filter.domain}
{else}
	<input name=adr_radio0 id=r00 type=radio onchange="javascript:set_first(0)" value=0 checked>
	<label for='r00'>Отдельный адрес</label>
	<SELECT name=mail0 id=mail0>
		{foreach from=$mails item=item name=outer}
			<option value={$item.id}>{$item.local_part}@{$item.domain}</option>
		{/foreach}
	</SELECT>
	<br>
{if count($domains)}
	<input name=adr_radio0 id=r01 type=radio onchange="javascript:set_second(0)" value=1>
	<label for='r01'>Домен полностью</label>
	<SELECT name=domain0 id=domain0 disabled=1>
		{foreach from=$domains item=item name=outer}
			<option value={$item}>{$item}</option>
		{/foreach}
	</SELECT>
{/if}
{/if}
</TD></TR>

<TR><TD class=left>
	Адрес отправителя:
</TD><TD>
	<input name=adr_radio1 id=r10 type=radio onchange="javascript:set_first(1)" value=0 checked>
	<label for='r10'>Отдельный адрес</label>
	<input type=text class=text name=mail1 id=mail1>
	<br>	
	<input name=adr_radio1 id=r11 type=radio onchange="javascript:set_second(1)" value=1>
	<label for='r11'>Домен полностью</label>
	<input type=text class=text name=domain1 id=domain1>
{if !($em_filter)}
	<br>
	<input name=adr_radio1 id=r12 type=radio onchange="javascript:set_third(1)" value=2>
	<label for='r12'>Любой адрес</label>
{/if}
</TD></TR>
</TBODY></TABLE>
<HR>
{literal}
<script language=javascript>
function set_first(p) {
	v='mail'+p;		document.all[v].disabled = 0;
	v='domain'+p;	document.all[v].disabled = 1;
	return 1;
}
function set_second(p) {
	v='mail'+p; 	document.all[v].disabled = 1;
	v='domain'+p;	document.all[v].disabled = 0;
	return 1;
}
function set_third(p) {
	v='mail'+p; 	document.all[v].disabled = 1;
	v='domain'+p;	document.all[v].disabled = 1;
	return 1;
}
</script>
{/literal}
<DIV align=center><INPUT class=button type=submit value="Добавить"></DIV>
</FORM>

<br><br>
