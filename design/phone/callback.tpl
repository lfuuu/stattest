<H2>Виртуальная АТС</H2>
<H3>Callback (обратный вызов)</H3>
Необходимо ввести номера, с которых возможен обратный звонок.<br>
{if count($phones_callback)}
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR><td class=header>Дата подключения</td><td class=header>Номер</td><td class=header>Тип обратного вызова</td><TD class=header>План доступных звонков</TD><TD class=header>Комментарии</TD><td>&nbsp;</TD></tr>
{foreach from=$phones_callback item=item name=outer}
<FORM action="?" id=form{$smarty.foreach.outer.iteration} name=form{$smarty.foreach.outer.iteration} method=get>
<input type=hidden name=action value=callback_change>
<input type=hidden name=module value=phone>
<input type=hidden name=id value='{$item.id}'>
<TR class="{cycle values='even,odd'}">
	<TD>{$item.actual_from}/</TD>
	<TD><input class=text type=text value='{$item.phone}' readonly="true" style='color:#808080'></TD>
	<TD><SELECT name="type_phone" class=text>
		<option value="dial_from_city"{if $item.type_phone == "dial_from_city"} selected{/if}>Входящие с городского номера</option>
    	<option value="dial_from_mobile"{if $item.type_phone == "dial_from_mobile"} selected{/if}>Входящие с мобильного номера</option>
	</SELECT></TD>
	<TD><SELECT name="type_dialplan" class=text>
    			<option value="local"{if $item.type_dialplan == "internal"} selected{/if}>Внутренние</option>
    			<option value="city"{if $item.type_dialplan == "city"} selected{/if}>Местные</option>
    			<option value="russia"{if $item.type_dialplan == "russia"} selected{/if}>Российские</option>
    			<option value="full"{if $item.type_dialplan == "full"} selected{/if}>Все</option>
  	</SELECT></TD>
	<TD><input type=text class=text style='width:100%' name=comment value="{$item.comment|htmlspecialchars}"></TD>
	<TD>
		<a href='#' onclick='form{$smarty.foreach.outer.iteration}.submit(); return false;'>изменить</a> 
		<a href='{$LINK_START}module=phone&action=callback_del&id={$item.id}'>удалить</a>
	</TD>
</TR></FORM>
{/foreach}
</TBODY></TABLE><HR>
{/if}
<h2>Новые номера</h2>
<table class=price cellSpacing=4 cellPadding=2 border=0>
<FORM action="?" method=get id=form name=form>
<input type=hidden name=action value=callback_add>	
<input type=hidden name=module value=phone>
<TR><td class=header>Номер</td>
<td class=header>Тип обратного вызова</td><TD class=header>План доступных звонков</TD>
<TD class=header>Коментарий</TD><td>&nbsp;</TD></tr>
<tr>
	<TD><input name=phone class=text value=''></TD>
	<TD>
		<SELECT name="type_phone" >
    			<option value="dial_from_city"  selected="selected" >Входящие с городского номера</option>
    			<option value="dial_from_mobile" >Входящие с мобильного номера</option>
  		</SELECT>
		
	</TD>
	<TD>
		<SELECT name="type_dialplan" >
    			<option value="internal" selected="selected" >Внутренние</option>
    			<option value="city" >Местные</option>
    			<option value="russia" >Российские</option>
    			<option value="full" >Все</option>
  		</SELECT>		
		
	</TD>
	<TD><textarea name="comment"  rows="1" cols="35"></textarea></TD>
	<TD><INPUT id=submit class=button type=submit value="Добавить"></TD>

</tr> <br>
<br>
</table>
</form>

{if count($del_phones)}
<hr>
<h3>Удаленные, отключенные номера</h3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0>
<TBODY>
<TR><td class=header>Дата подключения/ отключения</td><td class=header>Номер</td>
<td class=header>Тип обратного вызова</td><TD class=header>План доступных звонков</TD>
<TD class=header>Комментарии</TD><td>&nbsp;</TD></tr>

{foreach from=$del_phones item=item name=outer}
<TR class="{cycle values='even,odd'}">
	<TD>{$item.actual_from}/{$item.actual_to}</TD>
	<TD>{$item.phone}</TD>
	<TD>{$item.type_phone}</TD>
	<TD>{$item.type_dialplan}</TD>
	<TD>{$item.comment}</TD>
	<TD>
	</TD>
</TR>
</FORM>
{/foreach}
</TBODY></TABLE>
{/if}
