      <H2>Статистика</H2>
      <H3>Списание средств</H3>
Сумма по счетам за тот же период: {if $sum_bills!=""}{$sum_bills}{else}0{/if}<br>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="15%">Клиент</TD>
          <TD class=header vAlign=bottom width="20%">Услуга</TD>
          <TD class=header vAlign=bottom width="10%">Параметр</TD>
          <TD class=header vAlign=bottom width="20%">Сроки оказания услуги</TD>
          <TD class=header vAlign=bottom width="20%">Комментарий</TD>
          <TD class=header vAlign=bottom width="15%">Сумма</TD>
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>{$item.client}</td>
		<TD>{$item.service}</TD>
		<TD>{$item.param}</TD>
		<TD>{if ($item.date_from!=0)}{$item.date_from|date_format:"%Y-%m-%d"}{else}0000-00-00{/if} - {$item.date_to|date_format:"%Y-%m-%d"}</TD>
		<TD>{$item.comment}</TD>
		<TD><b>{$item.sum}</b></TD>
	</TR>
{/foreach}
</TBODY></TABLE>