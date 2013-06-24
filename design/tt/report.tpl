      <H2>Заявки</H2>
      <H3>Отчёт за период с {$tt_from|mdate:"Y-m-d"} по {$tt_to|mdate:"Y-m-d"}</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 border=0 width=750>
        <TR>
			<TD class=header style='background:none'>&nbsp;</TD>
			<TD class=header colspan=2 style='font-weight:bold'>потраченное время</TD>
			<TD class=header colspan=3 style='font-weight:bold'>просроченные этапы</TD>
			<TD class=header colspan=2 style='font-weight:bold'>этапы</TD>
		</TR><TR>
			<TD class=header vAlign=bottom>пользователь</TD>
			<TD class=header vAlign=bottom>реально</TD>
			<TD class=header vAlign=bottom>допустимо</TD>
			<TD class=header vAlign=bottom>время</TD>
			<TD class=header vAlign=bottom>открытых, шт</TD>
			<TD class=header vAlign=bottom>всего, шт</TD>
			<TD class=header vAlign=bottom>открытых, шт</TD>
			<TD class=header vAlign=bottom>всего, шт</TD>
        </TR>

{foreach from=$tt_report item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD><a href='{$LINK_START}module=users&m=user&id={$item.user}'>{$item.user}</a></TD>
		<TD align=right>{$item.time_total|time_period}</TD>
		<TD align=right>{$item.time_limit|time_period}</TD>
		<TD align=right>{$item.time_over|time_period}</TD>
		<TD align=right>{$item.n_over-$item.n_over_closed}</TD>
		<TD align=right>{$item.n_over}</TD>
		<TD align=right>{$item.n_open}</TD>
		<TD align=right>{$item.n_all}</TD>
	</TR>
{/foreach}
</TABLE>
