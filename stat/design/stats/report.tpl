      <H2>Статистика</H2>
      <H3>Интернет</H3>

      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom width="2%"></TD>
          <TD class=header vAlign=bottom width="2%"></TD>
          <TD class=header vAlign=bottom width="2%"></TD>
          <TD class=header vAlign=bottom width="24%">Клиент</TD>
          <TD class=header vAlign=bottom width="5%">Подключение</TD>
          <TD class=header vAlign=bottom width="15%">Тариф</TD>{if $show_tarif_traf}
		  <TD class=header vAlign=bottom width="15%">Трафик</TD>{/if}
          <TD class=header vAlign=bottom width="10%" style='text-align:right'>Входящий трафик, Мб</TD>
          <TD class=header vAlign=bottom width="10%" style='text-align:right'>Исходящий трафик, Мб</TD>
		  <TD class=header vAlign=bottom width="10%" style='text-align:right'>Входящий трафик/мес, Мб</TD>
          <TD class=header vAlign=bottom width="10%" style='text-align:right'>Исходящий трафик/мес, Мб</TD>
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
		<TD>
{if (isset($item.flags.in_less_out) && $item.flags.in_less_out) || ($newgen && $item.less_out_flag eq 'Y')}
         <span style="background-color:red;  display:inline; padding: 3px;">&nbsp;</span>
{/if}</td><td>
{if (isset($item.flags.over) && $item.flags.over) || ($newgen && $item.over_flag eq 'Y')}
         <span style="background-color:blue;  display:inline; padding: 3px;">&nbsp;</span>
{/if}</td><td>
{if (isset($item.flags.traf_less) && $item.flags.traf_less) || ($newgen && $item.traf_less_flag eq 'Y')}
         <span style="background-color:magenta;  display:inline; padding: 3px;">&nbsp;</span>
{/if}
        </TD>
		<TD><a href='{$LINK_START}module=clients&id={$item.client}'>{$item.client}</a></TD>
		<TD><a href="{$PATH_TO_ROOT}pop_services.php?table=usage_ip_ports&id={$item.id}" target="_blank">{$item.id}</a></TD>
		<TD>
{if isset($item.tarif.name)}
			<img alt='Текущий тариф' class=icon src='{$IMAGES_PATH}icons/tarif.gif' alt="{$item.tarif.mb_month}-{$item.tarif.pay_month}-{$item.tarif.pay_mb}">
			<span style='color:#0000C0' title='Текущий тариф: {$item.tarif.mb_month}-{$item.tarif.pay_month}-{$item.tarif.pay_mb}'>{$item.tarif.name}</span><br>
{elseif $newgen}
			<img alt='Текущий тариф' class=icon src='{$IMAGES_PATH}icons/tarif.gif' alt="{$item.mb_month}-{$item.pay_month}-{$item.pay_mb}">
			<span style='color:#0000C0' title='Текущий тариф: {$item.mb_month}-{$item.pay_month}-{$item.pay_mb}'>{$item.tarif_name}</span><br>
{/if}	</TD>{if $show_tarif_traf}
		<TD align=right>{$item.mb_month}</TD>{/if}
		<TD align=right>{fsize value=$item.in_bytes}</TD>
		<TD align=right>{fsize value=$item.out_bytes}</TD>
		<TD align=right>{fsize value=$totals[$item.id].in_bytes}</TD>
		<TD align=right>{fsize value=$totals[$item.id].out_bytes}</TD>
	</TR>
{/foreach}
</TBODY></TABLE>