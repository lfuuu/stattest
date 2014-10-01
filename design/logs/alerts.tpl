{if $pages > 1}
	{capture name="pagination"}
	
	<div class="pagination">
		Страница: 
		{section name=foo start=1 loop=$pages+1 step=1}
		{if $page == $smarty.section.foo.index}
			{assign var="selected" value=true}
		{else}
			{assign var="selected" value=false}
		{/if}
		<span {if $selected}class="selected"{/if}>
			{if !$selected}
				<a href="{$url}page={$smarty.section.foo.index}">
			{/if}
			{$smarty.section.foo.index}
			{if !$selected}
				</a>
			{/if}
		</span>
		{/section}
	</div>
	{/capture}
	{$smarty.capture.pagination}
{/if}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
	<TBODY>
		<TR>
			{if !$fixclient}
				<TD colspan="2"  class=header vAlign=bottom width="20%" >Клиент</TD>
			{/if}
			<TD  class=header vAlign=bottom width="18%">Дата/время</TD>
			<TD  class=header vAlign=bottom width="18%">Событие</TD>
			<TD  class=header vAlign=bottom width="11%">Контакт</TD>
			<TD  class=header vAlign=bottom width="11%">Баланс</TD>
			<TD  class=header vAlign=bottom width="11%">Лимит</TD>
			<TD  class=header vAlign=bottom width="11%">Значение</TD>
		</TR>

		{foreach from=$logs item=item key=key name=outer}
			<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if} {if !$item.is_set}style="color: #999999;"{/if}>
				{if !$fixclient}
					<TD align=left>{$item.client_id}</TD>
					<TD align=left><a href="index.php?module=clients&id={$item.client_id}">{$item.client}</a></TD>
				{/if}
				<TD align=left>{$item.timestamp|mdate:"d месяца Y H:i:s"}</TD>
				<TD align=left>
					{if !$item.is_set && $item.event != 'add_pay_notif' && $item.event != 'prebil_prepayers_notif'}
						Снято: 
					{/if}
					{$events_description[$item.event]}
				</TD>
				<TD align=left>
					{if $item.contact_type == "email"}
						<a style="font-weight:bold" href="mailto:{$item.contact_data}">{$item.contact_data}</a>
					{else}
						<span style="font-weight:bold">{$item.contact_data}</span>
					{/if}
				</TD>
				<TD align=right>{$item.balance|number_format:"2":",":" "}</TD>
				<TD align=right>{$item.limit|number_format:"0":",":" "}</TD>
				<TD align=right>{$item.value|number_format:"2":",":" "}</TD>
			</TR>
		{/foreach}
	</TBODY>
</TABLE>
{if $pages > 1}
	{$smarty.capture.pagination}
{/if}