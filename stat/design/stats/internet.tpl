<H2>Статистика</H2>
<H3>Интернет</H3>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
    <TBODY>
    <TR>
        <TD class=header vAlign=bottom width="34%">{if $detality=='ip'}IP-адресс{else}Дата/время{/if}</TD>
        <TD class=header vAlign=bottom width="33%" style='text-align:right'>Входящий трафик, мегабайты</TD>
        <TD class=header vAlign=bottom width="33%" style='text-align:right'>Исходящий трафик, мегабайты</TD>
    </TR>
    {foreach from=$stats.rows item=item key=key name=outer}
        <TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
            <TD>{if $detality=='ip'}{$item.ip}{else}{$item.tsf}{/if}</TD>
            <TD align=right><b>{fsize value=$item.in_bytes}</b></TD>
            <TD align=right><b>{fsize value=$item.out_bytes}</b></TD>
        </TR>
    {/foreach}
    <TR class=total>
        <TD>Итого</TD>
        <TD align=right><b>{fsize value=$stats.total.in_bytes}</b></TD>
        <TD align=right><b>{fsize value=$stats.total.out_bytes}</b></TD>
    </TR>
    </TBODY>
</TABLE>
