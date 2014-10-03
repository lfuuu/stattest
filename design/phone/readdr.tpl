<H2>Виртуальная АТС</H2>
<H3>Переадресация</H3>
В выбранное время звонки будут направляться на указанный Вами номер.<br>

{if count($phone_readdr)}
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TR><td colspan=2 style='background:none'>&nbsp;</td><td class=header colspan=7>Дни недели</td>
		<td class=header colspan=2>Время</td>
		<td colspan=1 style='background:none'>&nbsp;</td></TR>
<TR><td>&nbsp;</TD><td class=header>Номер</td><td class=header>пн</td><td class=header>вт</td><td class=header>ср</td><td class=header>чт</td><td class=header>пт</td><td class=header>сб</td><td class=header>вс</td>
		<td class=header>От</td>
		<td class=header>До</td>
		<td>&nbsp;</TD></TR>
{foreach from=$phone_readdr item=item name=outer}
<TR class={cycle values="even,odd"}><FORM action="?" method=post name=form>
<input type=hidden name=module value=phone>
<input type=hidden name=action value=readdr_save>
<input type=hidden name=id value="{$item.id}">
<TD><input type=checkbox name=enabled value=1{if $item.enabled} checked{/if}></td>
<TD><input type=text class=text name=phone value="{$item.phone}"></td>
<td><input type=checkbox name=day0 value=1{if $item.day0} checked{/if}></td>
<td><input type=checkbox name=day1 value=1{if $item.day1} checked{/if}></td>
<td><input type=checkbox name=day2 value=1{if $item.day2} checked{/if}></td>
<td><input type=checkbox name=day3 value=1{if $item.day3} checked{/if}></td>
<td><input type=checkbox name=day4 value=1{if $item.day4} checked{/if}></td>
<td><input type=checkbox name=day5 value=1{if $item.day5} checked{/if}></td>
<td><input type=checkbox name=day6 value=1{if $item.day6} checked{/if}></td>
<TD><input style='width:60px' type=text class=text name=time_from value="{$item.time_from}"></td>
<TD><input style='width:60px' type=text class=text name=time_to value="{$item.time_to}"></td>
<td><a href='#' onclick='if (this.parentNode.parentNode.nodeName=="FORM") this.parentNode.parentNode.submit(); else this.parentNode.parentNode.childNodes[0].submit();'>изменить</a> <a href='?module=phone&action=readdr_del&id={$item.id}'>удалить</a></td>
</form>
</tr>
{/foreach}
</table>
{/if}

<h3>Добавить</h3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TR><td colspan=2 style='background:none'>&nbsp;</td><td class=header colspan=7>Дни недели</td>
		<td class=header colspan=2>Время</td>
		<td colspan=1 style='background:none'>&nbsp;</td></TR>
<TR><td>&nbsp;</TD><td class=header>Номер</td><td class=header>пн</td><td class=header>вт</td><td class=header>ср</td><td class=header>чт</td><td class=header>пт</td><td class=header>сб</td><td class=header>вс</td>
		<td class=header>От</td>
		<td class=header>До</td>
		<td>&nbsp;</TD></TR>
<TR class=odd>
<FORM action="?" method=post>
<input type=hidden name=module value=phone>
<input type=hidden name=action value=readdr_save>
<input type=hidden name=id value="">
<TD><input type=checkbox name=enabled value=1 checked></td>
<TD><input type=text class=text name=phone value=""></td>
<td><input type=checkbox name=day0 value=1 checked></td>
<td><input type=checkbox name=day1 value=1 checked></td>
<td><input type=checkbox name=day2 value=1 checked></td>
<td><input type=checkbox name=day3 value=1 checked></td>
<td><input type=checkbox name=day4 value=1 checked></td>
<td><input type=checkbox name=day5 value=1 checked></td>
<td><input type=checkbox name=day6 value=1 checked></td>
<TD><input style='width:60px' type=text class=text name=time_from value=""></td>
<TD><input style='width:60px' type=text class=text name=time_to value=""></td>
<td><input type=submit class=button value='Добавить'></td>
</form>
</tr>
</table>