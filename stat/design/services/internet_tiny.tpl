{if isset($internet_suffix) && $internet_suffix=="collocation"}
{assign var=actprefix value=co}
{else}
{assign var=actprefix value=in}
{/if}

{if count($services_conn) || !isset($is_secondary_output)}
{if !isset($is_secondary_output)}
<H2>Услуги</H2>
<H3>{if $internet_suffix!="collocation"}Интернет{else}Collocation{/if} подключения</H3>
{else}
<H3><a href='?module=services&action={$actprefix}_view'>{if $internet_suffix!="collocation"}Интернет{else}Collocation{/if} подключения</a></H3>
{/if}
<div border="1">
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0>
<TBODY>
<tr>
	<TD style='background-color:#FFFFD8' class=header vAlign=bottom width="3%">&nbsp;</TD>
{if isset($show_client)}
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="13%">клиент</TD>
{/if}
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="6%">подключение</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="30%">адрес</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="9%">узел::порт</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="13%">тариф</TD>
	<TD onmouseover="javascript: menu_item(this,1);" onmouseout="javascript: menu_item(this,0);" style='background-color:#FFFFD8' class=header vAlign=bottom width="16%">сети</TD>
</tr>
{foreach from=$services_conn item=conn name=outer}{if $conn.data.client}
<TR bgcolor="{if $conn.data.status=='working'}{if $conn.data.actual}#EEDCA9{else}#fffff5{/if}{else}#ffe0e0{/if}">
	<TD>{$smarty.foreach.outer.iteration}</TD>
{if isset($show_client)}
	<TD><a href='/client/view?id={$conn.data.clientid}'>{$conn.data.client}</a></td>
{/if}
	<TD>&#8470;{$conn.data.id}</td>
	<td style='font-size:85%'>{$conn.data.address}</td>
	<td>{if $conn.data.port=='mgts'}{$conn.data.node}{else}<a href='{$LINK_START}module=routers&id={$conn.data.node}'>{$conn.data.node}</a>{/if}::{$conn.data.port}</td>
	<td style='font-size:85%'>
		{if isset($conn.data.tarif.name)}{$conn.data.tarif.mb_month}-{$conn.data.tarif.pay_month}-{$conn.data.tarif.pay_mb}{/if}	
		{if isset($conn.data.tarif_old.name)}<br>{$conn.data.tarif_old.mb_month}-{$conn.data.tarif_old.pay_month}-{$conn.data.tarif_old.pay_mb}{/if}
		</td>
	<td>
{foreach from=$conn.nets item=net name=inner}{if $net.actual}
	{ipstat net=$net.net}
{/if}{/foreach}	
{foreach from=$conn.nets item=net name=inner}{if !$net.actual}
	{ipstat net=$net.net color=#B0B0B0}
{/if}{/foreach}
	</td>
</TR>

{/if}{/foreach}
</tbody>
</table>
</div>

{if !isset($is_secondary_output)}
<br><br>
{if $internet_suffix=="collocation"}
{if access_action('services','co_add')}<a href='{$LINK_START}module=services&action={$actprefix}_add'>Добавить подключение</a>{/if}
{else}
{if access_action('services','in_add')}<a href='{$LINK_START}module=services&action={$actprefix}_add'>Добавить подключение</a>{/if}
{/if}

{/if}
{/if}
