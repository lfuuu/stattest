<H2>Мониторинг</H2>
<H3>Список VIP-клиентов</H3>
{if access('monitoring','edit')}
	<a href='{$LINK_START}module=monitoring&action=add'>Добавить</a>
{/if}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR>
	<TD class=header vAlign=bottom>клиент/роутер</TD>
	<TD class=header vAlign=bottom><b>число неудачных пингов</b></TD>
	<TD class=header vAlign=bottom>e-mail</TD>
	<TD class=header vAlign=bottom>телефон</TD>
	<TD class=header vAlign=bottom>&nbsp;</TD>
</TR>
{foreach from=$monitoring item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==count($monitoring)%2}even{else}odd{/if}>
{if $item.client}
	<TD vAlign=middle><a href="{$LINK_START}module=clients&id={$item.client}">{$item.client}</a></TD>
{else}
	<TD vAlign=middle><a href="{$LINK_START}module=routers&id={$item.router}">{$item.router}</a> (роутер)</TD>
{/if}
	<TD vAlign=middle>{$item.num_unsucc}</TD>
	<TD vAlign=middle>{$item.email}</TD>
	<TD vAlign=middle>{$item.phone}</TD>
	<TD vAlign=middle>
{if access('monitoring','edit')}
	<a href='{$LINK_START}module=monitoring&action=edit&id={$item.id}'>редактировать</a>
	<a href='{$LINK_START}module=monitoring&action=apply&dbaction=delete&keys[id]={$item.id}'>удалить</a>
{/if}
	
	&nbsp;</TD>
</TR>
{/foreach}
</TABLE>