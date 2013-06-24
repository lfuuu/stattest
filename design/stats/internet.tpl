      <H2>Статистика</H2>
      <H3>Интернет</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="25%">Дата/время</TD>
{if $is_collocation}
          <TD class=header vAlign=bottom width="7%" style='text-align:right'>вход. Россия, мб</TD>
          <TD class=header vAlign=bottom width="6%" style='text-align:right'>исх. Россия, мб</TD>
          <TD class=header vAlign=bottom width="6%" style='text-align:right'>вход. Россия2, мб</TD>
          <TD class=header vAlign=bottom width="7%" style='text-align:right'>исх. Россия2, мб</TD>
          <TD class=header vAlign=bottom width="6%" style='text-align:right'>вход. заруб, мб</TD>
          <TD class=header vAlign=bottom width="6%" style='text-align:right'>исх. заруб, мб</TD>
{else}
          <TD class=header vAlign=bottom width="25%" style='text-align:right'>Входящий трафик, мегабайты</TD>
          <TD class=header vAlign=bottom width="25%" style='text-align:right'>Исходящий трафик, мегабайты</TD>
{/if}
{if $detality=='ip'}          <TD class=header vAlign=bottom width="25%">IP-адрес</TD>{/if}
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>{$item.tsf}</TD>
{if $is_collocation}
		<TD align=right><b>{fsize value=$item.in_r}</b></TD>
		<TD align=right><b>{fsize value=$item.out_r}</b></TD>
		<TD align=right><b>{fsize value=$item.in_r2}</b></TD>
		<TD align=right><b>{fsize value=$item.out_r2}</b></TD>
		<TD align=right><b>{fsize value=$item.in_f}</b></TD>
		<TD align=right><b>{fsize value=$item.out_f}</b></TD>
{else}
		<TD align=right><b>{fsize value=$item.in_bytes}</b></TD>
		<TD align=right><b>{fsize value=$item.out_bytes}</b></TD>
{/if}
{if $detality=='ip'}		<TD>{$item.ip}</TD>{/if}
	</TR>
{/foreach}
</TBODY></TABLE>
