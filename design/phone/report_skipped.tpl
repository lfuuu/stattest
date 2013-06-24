      <H2>Виртуальная АТС</H2>
      <H3>Пропущенные звонки</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="20%">Дата/время</TD>
{if $detality=='call'}
          <TD class=header vAlign=bottom width="22%">Кто звонил</TD>
          <TD class=header vAlign=bottom width="23%">Кому звонил</TD>
          <TD class=header vAlign=bottom width="15%">Причина завершения</TD>
{else}
          <TD class=header vAlign=bottom width="15%">Число пропущенных звонков</TD>
{/if}
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>{$item.tsf1}</TD>
{if $detality=='call'}
		<TD>{$item.num_from}</TD>
		<TD>{$item.num_to}</TD>
		<TD>{$item.cause}</TD>
{else}		
		<TD>{$item.cnt}</TD>
{/if}
	</TR>
{/foreach}
</TBODY></TABLE>
