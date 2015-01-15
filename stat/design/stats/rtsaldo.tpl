      <H2>Статистика</H2>
      <H3>Списание средств</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom>Дата</TD>
          <TD class=header vAlign=bottom>Изменение</TD>
          <TD class=header vAlign=bottom>Получившаяся сумма</TD>
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>{$item.ts|mdate:'d месяца Y'}</TD>
		<TD>{$item.delta_USD}$, {$item.delta_RUB}р</TD>
		<TD>{$item.sum_USD}$, {$item.sum_RUB}р</TD>
	</TR>
{/foreach}
</TBODY></TABLE>