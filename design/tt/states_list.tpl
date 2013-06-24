      <H2>Заявки</H2>
      <h3>tt_states</h3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="12%">id</TD>
          <TD class=header vAlign=bottom width="32%">название</TD>
          <TD class=header vAlign=bottom width="22%">порядок</TD>
          <TD class=header vAlign=bottom width="22%">изменение времени</TD>
          <TD>&nbsp;</td>
		</TR>
{foreach from=$tt_states item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
          <TD><a href='{$LINK_START}module=tt&action=sedit&id={$item.id}'>{$item.id}</a></TD>
          <TD>{$item.name}</TD>
          <TD>{$item.order}</TD>
          <TD>{if $item.time_mark}{$item.time_mark|mdate:'Y-m-d H:i:s'} &ndash; {/if}{$item.time_delta}</TD>
          <TD><a href='{$LINK_START}module=tt&action=sapply&dbaction=delete&keys[id]={$item.id}'>удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
<a href='{$LINK_START}module=tt&action=sadd'>Добавить состояние</a><br>
