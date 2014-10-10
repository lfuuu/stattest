<H2>Курьеры</H2>

<FORM action="?" method=get id=form name=form>
<input type=hidden name=module value=employeers>
<input type=hidden name=action value=couriers>
<input type=hidden name="cId" value="{$cId}">
{if $cId != 0}Редактирование{else}Добавление{/if}: <br>
<table><tr><td>Имя: </td><td><input type="text" value="{$cName}" name="cName"></td></tr>
<tr><td>Телефон: </td><td><input type="text" value="{$cPhone}" name="cPhone"></td></tr>
<tr><td>All4Geo ид: </td><td><input type="text" value="{$cAll4geo}" name="cAll4geo"></td></tr>
</table>
<input type=submit value='Сохранить' class=button>
</form>

<h3>Список курьеров</h3>
<TABLE class=price cellSpacing=4 cellPadding=2 border=0 width="100%">
<TBODY>
<TR><TD class=header vAlign=bottom>Имя</TD>
<TD class=header vAlign=bottom>All4geo Id</TD>
<TD class=header vAlign=bottom>Телефон</TD>
<TD class=header vAlign=bottom width="1%"></TD></TR>

{foreach from=$l_couriers item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
<TD valign=center><a href='{$LINK_START}module=employeers&action=couriers&id={$item.id}'>{$item.name}</a></TD>
<TD valign=center>{$item.all4geo}</TD>
<TD valign=center>{$item.phone}</TD>
<TD><a href='{$LINK_START}module=employeers&action=couriers&del={$item.id}'>x</a></TD></TR>
{foreachelse}
<tr><td colspan=3 align=center><i>Список пуст</i></td></tr>
{/foreach}
</TBODY></TABLE>
