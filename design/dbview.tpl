<h2>{$dbview_headers}</h2>
{foreach from=$dbview_filters key=key item=group name=outer}
<b>{$key}</b>:
	{foreach from=$group item=item name=inner}
		{if !$item.selected}
			<a href='{$LINK_START}{$dbview_link_read}{foreach from=$dbview_filters item=g key=gk name=g}&filter[{$smarty.foreach.g.iteration}]={if $gk==$key}{$item.value}{else}{foreach from=$g item=f name=f}{if $f.selected}{$f.value}{/if}{/foreach}{/if}{/foreach}'>{$item.title}</a>
		{else}
			{$item.title}
		{/if}
	{/foreach}
	<br>
{/foreach}
<a href='{$LINK_START}{$dbview_link_edit}'>Добавить</a>
<TABLE class=price cellSpacing=2 cellPadding=1 width="100%" border=0>
{if count($dbview_fieldgroups)}
<TR>{foreach from=$dbview_fieldgroups item=item key=key}<TD colspan={$item.0} {if $item.1}class=header style='font-weight:bold'{else}style='background:none'{/if}>{$item.1}</TD>{/foreach}</TR>
{/if}
<TR>{foreach from=$dbview_fields item=item key=key}<TD class=header>{$item}</TD>{/foreach}</TR>

{foreach from=$dbview_data item=item key=key name=outer}
<TR class='{if isset($item._tr_class)}{$item._tr_class}{else}{cycle values="even,odd"}{/if}'>
{foreach from=$dbview_fields item=itemF key=keyF name=inner}
{if $smarty.foreach.inner.iteration==1}
<TD><a href='{$LINK_START}{$dbview_link_edit}&id={$item.id}'>{$item.$keyF}</TD>
{else}
<TD>{$item.$keyF}</TD>
{/if}
{/foreach}
</TR>
{/foreach}
</TABLE>