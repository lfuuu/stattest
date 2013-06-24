      <H2>Статистика</H2>
      <H3>VPN</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="25%">Дата/время</TD>
          <TD class=header vAlign=bottom width="25%" style='text-align:right'>Входящий трафик, мегабайты</TD>
          <TD class=header vAlign=bottom width="25%" style='text-align:right'>Исходящий трафик, мегабайты</TD>
{if $detality=='ip'}          <TD class=header vAlign=bottom width="25%">IP-адрес</TD>{/if}
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>{$item.tsf}</TD>
		<TD align=right><b>{fsize value=$item.in_bytes}</b></TD>
		<TD align=right><b>{fsize value=$item.out_bytes}</b></TD>
{if $detality=='ip'}		<TD>{$item.ip}</TD>{/if}
	</TR>
{/foreach}
</TBODY></TABLE>
