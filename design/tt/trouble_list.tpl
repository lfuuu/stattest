{if !isset($hide_tt_list) || $hide_tt_list == 0}

{if !isset($hide_tts)}{if !isset($tt_wo_explain) && $tt_design=='full'}<H2>{$tt_header}
	{if $fixclient_data}(клиент <a href='{$LINK_START}module=clients&id={$fixclient_data.client}'>{$fixclient_data.client}</a>){/if}
	</H2>
{elseif $tt_design=='client'}
	<H3><a href='{$LINK_START}module=tt&action=list&mode=1'>{$tt_header}</a></H3>
{/if}

{if $tt_design == "full"}
Найдено {$pager_all} заявок<br>
    {if count($pager_pages)>1}
        Страницы: {foreach from=$pager_pages item=i} {if $pager_page == $i} {$i} {else} <a href='{$pager_url}&page={$i}&filtred=true'>{$i}</a>{/if} {/foreach}<br>
    {/if}
{else}
    {if $pager_all}Показано заявок: {if $pager_all > $pager_page_size}{$pager_page_size} из {$pager_all}{else} {$pager_all}{/if}{/if}
{/if}
<TABLE class={if $tt_design=='service'}insblock{else}price{/if} cellSpacing=2 cellPadding=2 width="{if $tt_design=='service'}700px{else}100%{/if}" border=0>
<TBODY>
{if $tt_design == "full"}
<tr>
    <td class=header>{sort_link sort=1 text='&#8470;' link=$CUR sort_cur=$sort so_cur=$so}</td>
    <td class=header>Дата создания</td>
    <td class=header>{sort_link sort=3 text='Этап' link=$CUR sort_cur=$sort so_cur=$so}</td>
    <td class=header>{sort_link sort=3 text='Ответ.' link=$CUR sort_cur=$sort so_cur=$so}</td>
    <td class=header>Проблема</td>
</tr>
<tr>
    <td class=header nowrap>Тип заявки</td>
    <td class=header>в работе</td>
    <td class=header>{sort_link sort=2 text='Клиент' link=$CUR sort_cur=$sort so_cur=$so}</td>
    <td class=header>Услуга</td>
    <td class=header>Последний коментарий</td>
</tr>
{/if}
{if false}
<TR>
	{if $tt_design=='full'}
		<TD class=header vAlign=bottom>{sort_link sort=1 text='&#8470;' link=$CUR sort_cur=$sort so_cur=$so}</TD>
		{if $tt_subject<2}
			{if $tt_subject<1}<TD class=header vAlign=bottom>{sort_link sort=2 text='Клиент' link=$CUR sort_cur=$sort so_cur=$so}</TD>{/if}
			<TD class=header vAlign=bottom>Услуга</TD>

		{/if}
		<TD class=header vAlign=bottom>Тип заявки</TD>
		<TD class=header vAlign=bottom>{sort_link sort=3 text='Этап' link=$CUR sort_cur=$sort so_cur=$so}</TD>
		<TD class=header vAlign=bottom>{sort_link sort=4 text='Ответ.' link=$CUR sort_cur=$sort so_cur=$so}</TD>
	{else}
		{if $tt_subject<2}
			{if $tt_subject<1}<TD class=header vAlign=bottom><a href="./?module=tt&action=list&mode=0&sort=2&so=1">клиент</a></TD>{/if}
			<TD class=header vAlign=bottom>услуга</TD>
		{/if}
		<TD class=header vAlign=bottom>Тип заявки</TD>
		<TD class=header vAlign=bottom><a href="./?module=tt&action=list&mode=0&sort=3&so=0">этап</a></TD>
		<TD class=header vAlign=bottom>ответ.</TD>
	{/if}
	<TD class=header vAlign=bottom width=45%>проблема</TD>
	<TD class=header vAlign=bottom>сроки</TD>
</TR>
{/if}
{foreach from=$tt_troubles item=r name=outer}
<tr class={if $smarty.foreach.outer.iteration%2==count($tt_troubles)%2}even{else}odd{/if}{if $r.is_important} style="background-color: #f4c0c0;"{/if}>
    <td colspan=1><a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'><b>{$r.trouble_id}</b></a></td>
    <td colspan=1 nowrap style="font-size:85%;">{mformat param=$r.date_creation format='Y.m.d H:i'}</td>
    <td colspan=1>{$r.state_name}</td>
    <td colspan=1>{$r.user_main}</td>
    <td colspan=1 style="font-size:85%">{$r.problem|replace:"\\r":""|replace:"\\n":" "}</td>
</tr>
<tr class={if $smarty.foreach.outer.iteration%2==count($tt_troubles)%2}even{else}odd{/if} style="border: 1px solid black;{if $r.is_important}background-color: #f4c0c0;{/if}">
    <td colspan=1>{$trouble_subtypes_list[$r.trouble_subtype]}</td>
    <td colspan=1 style="font-size:85%;">{$r.time_pass}</td>
    <td colspan=1><a href='{$LINK_START}module=clients&id={$r.client_orig}'>{$r.client}</a></td>
    <td colspan=1 align=center style='font-size:85%;{if !$r.service && $r.bill_no && $r.is_payed == 1}background-color: #ccFFcc;{/if}'>
        {if $r.service}<a href='pop_services.php?table={$r.service}&id={$r.service_id}'>{$r.service|replace:"usage_":""}<br>{$r.service_id}</a>
	    {elseif $r.bill_no}<a href="?module=newaccounts&action=bill_view&bill={$r.bill_no}" style="font-size:100%;font-weight: bold">{$r.bill_no}</a>
        {else}&nbsp;{/if}</td>
    <td colspan=1 style="font-size:85%;">{$r.last_comment}</td>
</tr>
<tr class={if ($smarty.foreach.outer.iteration+1)%2==count($tt_troubles)%2}even{else}odd{/if} ><td colspan=5 style="border-top: 1px solid #aaa;font-size:1pt;">&nbsp;</td></tr>
{if false}
<TR class=odd{*if $smarty.foreach.outer.iteration%2==count($tt_troubles)%2}even{else}odd{/if*}{if $r.is_important} style="background-color: #f4c0c0;"{/if}>
	{if $tt_design=='full'}<TD><a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'>{$r.trouble_id}</a></TD>{/if}
{if $tt_subject<2}
	{if $tt_subject<1}<TD><a href='{$LINK_START}module=clients&id={$r.client_orig}'>{$r.client}</a></TD>{/if}
	<TD align=center style='font-size:85%;{if !$r.service && $r.bill_no && $r.is_payed == 1}background-color: #ccFFcc;{/if}'>{if $r.service}
		<a href='pop_services.php?table={$r.service}&id={$r.service_id}'>{$r.service|replace:"usage_":""}<br>{$r.service_id}</a>
	{elseif $r.bill_no}<a href="?module=newaccounts&action=bill_view&bill={$r.bill_no}" style="font-size:medium;font-weight: bold">{$r.bill_no}</a>{else}&nbsp;{/if}</TD>
{/if}
    <td>{$trouble_subtypes_list[$r.trouble_subtype]}</td>
	<TD><a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'>{$r.state_name}</a>
		{if $showStages && $r.state_id!=2}<a href='javascript:toggle2("tt_main{$smarty.foreach.outer.iteration}")'>&raquo;&raquo;</a>{/if}</TD>
	<TD>{$r.user_main}</TD>
	<TD style='font-size:85%'>{$r.problem}</TD>
	<TD style="font-size:85%;{if $r.is_sms_send}background-color: yellow;{/if}">
    {if $r.state_id==2}
		{mformat param=$r.date_start format='Y.m.d H:i'}<br>
		{mformat param=$r.date_edit format='Y.m.d H:i'}
	{elseif $r.state_id==4}
			выезд {mformat param=$r.date_start format='Y.m.d H:i'}<br>
			{if $r.is_active}
				прошло <font color=red>{$r.time_pass} / {$r.time_limit}</span>
			{/if}
	{else}
		{mformat param=$r.date_start format='Y.m.d H:i'}<br>
		{if $r.is_active}
			прошло <font color=red>{$r.time_pass} / {$r.time_limit}</span>
		{else}
			неактивна / {$r.time_pass}
		{/if}
	{/if}</TD>
</TR>
{/if}
{if $showStages && $r.state_id!=2}
	<TR class=odd {if !isset($bill) || $r.bill_no<>$bill.bill_no}style='display:none'{/if} id='tt_main{$smarty.foreach.outer.iteration}'><TD colspan=6>
	<TABLE class={if $tt_design=='service'}insblock{else}price{/if} cellSpacing=4 cellPadding=2 width="100%" border=0><TBODY>
	<TR>
		<TD class=header vAlign=bottom width="9%">Состояние</TD>
		<TD class=header vAlign=bottom width="8%">Ответственный</TD>
		<TD class=header vAlign=bottom width="60%">Комментарий</TD>
		<TD class=header vAlign=bottom width="8%">кто</TD>
		<TD class=header vAlign=bottom width="15%">когда</TD>
	</TR>
	{foreach from=$r.stages item=r2 name=inner}
	<TR class={if $smarty.foreach.inner.iteration%2==count($r.stages)%2}even{else}odd{/if}>
		<TD>{$r2.state_name}</TD>
		<TD>{$r2.user_main}</TD>
		<TD>{$r2.comment}</TD>
		<TD style='font-size:85%'>{$r2.user_edit}</TD>
		<TD style='font-size:85%'>{mformat param=$r2.date_edit format='Y.m.d H:i'}</TD>
	</TR>
	{/foreach}
	</TBODY></TABLE></TD></TR>
{/if}
{/foreach}
</TBODY></TABLE>

{if $tt_design == "full"}
{if count($pager_pages)>1}
Страницы: {foreach from=$pager_pages item=i} {if $pager_page == $i} {$i} {else} <a href='{$pager_url}&page={$i}&filtred=true'>{$i}</a>{/if} {/foreach}<br>
{/if}
{/if}



{/if}
{/if}
