<H2>Виртуальная АТС</H2>
<H3>Короткие номера</H3>
При вводе короткого трёхзначного номера будет вызываться другой, выбранный вами телефонный номер.<br>
Не рекомендуется вводить номера специальных служб: 100 (служба времени), 009 (справочная) и других.<br>
{if count($phones_short)}
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR><td class=header>Короткий номер</td><td class=header>Какому номеру соответствует</td><td>&nbsp;</td></tr>
{foreach from=$phones_short item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==count($phones_short)%2}even{else}odd{/if}>
	<TD>{$item.phone_short}</TD>
	<TD>{$item.phone}</TD>
	<TD><a href='{$LINK_START}module=phone&action=short_del&phone_short={$item.phone_short}'>удалить</a>
</TR>
</FORM>
{/foreach}
</TBODY></TABLE>
{/if}
<br><br>
<FORM action="?" method=get id=form name=form>
<input type=hidden name=action value=short_add>	
<input type=hidden name=module value=phone>
<table><tr><td>
	<TABLE class=mform cellSpacing=4 cellPadding=2 border=0><TBODY>
<TR><TD class=left>Короткий номер:</td><td><input class=text name=phone_short value='{$phone_short}'></td></tr>
<tr><td class=left>На какой номер звонить:</td><td><input class=text name=phone value='{$phone}'></td></tr>
</tbody></table>
<INPUT id=submit class=button type=submit value="Добавить"><br>
</form>
