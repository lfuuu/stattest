      <H2>Статистика</H2>
      <H3>PPP</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="20%">Дата/время</TD>
          <TD class=header vAlign=bottom width="20%">Время он-лайн</TD>
          <TD class=header vAlign=bottom width="20%" style='text-align:right'>Входящий трафик, мегабайты</TD>
          <TD class=header vAlign=bottom width="20%" style='text-align:right'>Исходящий трафик, мегабайты</TD>
{if $detality=="sessions" || $detality=="login"}
          <TD class=header vAlign=bottom width="20%">Логин</TD>
{/if}
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>{$item.tsf1}</TD>
		<TD>{$item.tsf2}</TD>
		<TD align=right><b>{fsize value=$item.out_bytes}</b></TD>
		<TD align=right><b>{fsize value=$item.in_bytes}</b></TD>
{if $detality=="sessions" || $detality=="login"}
		<TD>{$item.login}</TD>
{/if}
	</TR>
{/foreach}
</TBODY></TABLE>
