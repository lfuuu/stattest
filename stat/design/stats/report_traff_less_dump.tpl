      <H2>Статистика</H2>
      <H3>Интернет</H3>

      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="2%"></TD>
          <TD class=header vAlign=bottom width="24%">Клиент</TD>
          <TD class=header vAlign=bottom width="15%">Подключение</TD>
          <TD class=header vAlign=bottom width="15%">Тариф</TD>
          <TD class=header vAlign=bottom width="15%" style='text-align:right'>Входящий трафик, Мб</TD>
          <TD class=header vAlign=bottom width="15%" style='text-align:right'>Исходящий трафик, Мб</TD>
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>&nbsp;</TD>
		<TD><a href='client/view?id={$item.client}'>{$item.client}</a></TD>
		<TD><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_ip_ports&id={$item.id}" target="_blank">{$item.id}</a></TD>
		<TD>
{if isset($item.tarif.name)}<img alt='Текущий тариф' class=icon src='{$IMAGES_PATH}icons/tarif.gif' alt="{$item.tarif.mb_month}-{$item.tarif.pay_month}-{$item.tarif.pay_mb}">
		<span style='color:#0000C0' title='Текущий тариф: {$item.tarif.mb_month}-{$item.tarif.pay_month}-{$item.tarif.pay_mb}'>{$item.tarif.name}</span><br>{/if}
			</TD>
		<TD align=right>{fsize value=$item.sum_in}</TD>
		<TD align=right>{fsize value=$item.sum_out}</TD>
	</TR>
{/foreach}
</TBODY></TABLE>
