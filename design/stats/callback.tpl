      <H2>Статистика</H2>
      <H3>Callback</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
<TD style='background:none'>&nbsp;</TD>
{if $detality=='call'}
<TD style='background:none'>&nbsp;</TD>
<TD style='background:none'>&nbsp;</TD>
{/if}
<TD style='background:none'>&nbsp;</TD>
<TD class=header vAlign=bottom style='font-weight:bold' colspan=3>Стоимость разговора</TD>
        </TR><TR>
          <TD class=header vAlign=bottom>Дата/время</TD>
{if $detality=='call'}
          <TD class=header vAlign=bottom>Кто звонил</TD>
          <TD class=header vAlign=bottom>Кому звонил</TD>
{/if}
          <TD class=header vAlign=bottom>Время разговора</TD>
          <TD class=header vAlign=bottom>Кто</TD>
          <TD class=header vAlign=bottom>Кому</TD>
          <TD class=header vAlign=bottom>Итог</TD>
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>{$item.tsf1}</TD>
{if $detality=='call'}
		<TD>{$item.num_from}</TD>
		<TD>{$item.num_to}</TD>
{/if}
		<TD>{$item.tsf2}</TD>
		<TD>{$item.priceFrom}</TD>
		<TD>{$item.priceTo}</TD>
		<TD>{$item.price}</TD>
	</TR>
{/foreach}
</TBODY></TABLE>
