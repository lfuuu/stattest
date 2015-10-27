{if !isset($hide_tts)}{if !isset($tt_wo_explain) && $tt_design=='full'}<H2>{$tt_header}
	{if $fixclient_data}(клиент <a href='/client/view?id={$fixclient_data.id}'>{$fixclient_data.client}</a>){/if}
	</H2>
{elseif $tt_design=='client'}
	<H3><a href='{$LINK_START}module=tt&action=list&mode=1'>{$tt_header}</a></H3>
{/if}
Найдено {$pager_all} заявок<br>
{if count($pager_pages)>1}
Страницы: {foreach from=$pager_pages item=i} {if $pager_page == $i} {$i} {else} <a href='{$pager_url}&filtred=true&page={$i}'>{$i}</a>{/if} {/foreach}<br>
{/if}
<style>
{literal}
#trouble_table td {
    font-size: 8pt;
}
#tt_stable td{
    padding: 0px 2px 0px 2px;
    vertical-align:top;
}
{/literal}
</style>

<TABLE class={if $tt_design=='service'}insblock{else}{/if} cellSpacing=4 cellPadding=2 width="{if $tt_design=='service'}700px{else}100%{/if}" border=0>
{foreach from=$tt_troubles item=r name=outer}
<tr>
<td colspan=7 style="background-color:{cycle values="#E5E5E5,#F5F5F5"};{if $r.is_important}background-color: #f4c0c0;{/if}">

<table border=0 width=99% id="trouble_table">
    <tr>
        <td rowspan=2 width=20% valign=top align=center nowrap>
{$r.date_creation|udate_with_timezone} / {$r.user_author}<br><br>
 <a href="?module=tt&action=view&id={$r.id}" style="font-size:10pt;font-weight: bold">{$r.id}</a> / {$trouble_subtypes_list[$r.trouble_subtype]} / {$r.user_main}<br>
        <br>
        
        <br><br>
            <span title="заявка в работе">{$r.time_start}</span>{if $r.state_id != 2} / <span title="на текушей стадии">{$r.time_pass}</span>{/if}
        </td>
        <td>
        
            <a href='/client/view?id={$r.clientid}'>{$r.clientid}{if $r.client != $r.client_orig} ({$r.client_orig}){/if}</a> / {$r.manager} / <a href='/client/view?id={$r.clientid}'><b>{$r.company}</b></a>:<br>
            {if $r.service}<a href='pop_services.php?table={$r.service}&id={$r.service_id}'>{$r.service|replace:"usage_":""}: {$r.service_id}</a>/
        {elseif $r.bill_no}<a href="?module=newaccounts&action=bill_view&bill={$r.bill_no}" style="font-size:11pt;font-weight: bold">{$r.bill_no}</a>/{/if}
        {$r.problem|escape}
            <hr>
        </td>
    </tr>
    <tr>
        <td valign=top>
        <style>
{literal}
 td{
        }
{/literal}
        </style>
        <table style="border-collapse:collapse" cellPadding=0 cellSpacing=0 border=0 id="tt_stable">
            {foreach from=$r.stages item=t}
            <tr style="border-bottom: 1px solid #EDEDED;">
<td nowrap style="font-size: 8pt;">{$t.date_start}</td><td><a href='./?module=tt&action=view&id={$t.trouble_id}'>{$t.state_name}</a></td><td> {$t.user_main}/{$t.user_edit}</td><td> {$t.comment|escape}
        {if $t.doers} {foreach from=$t.doers item=d}----><b>{$d.depart} {$d.name} ({$r.date_start}){if $r.sms} <br><span style="color: #c40000;">{$r.sms.sms_send|udate_with_timezone} // {$r.sms.sms_sender}</span>{/if}</b>{/foreach}{/if}
        </td></tr>
            {/foreach}
        </table>
        </td>
    </tr>
    </table>
</td>
</tr>
{if false}
<TR class={if $smarty.foreach.outer.iteration%2==count($tt_troubles)%2}even{else}odd{/if}{if $r.is_important} style="background-color: #f4c0c0;"{/if}>
	{if $tt_design=='full'}<TD><a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'>{$r.trouble_id}</a></TD>{/if}
{if $tt_subject<2}
	{if $tt_subject<1}<TD><a href='/client/view?id={$r.clientid}'>{$r.clientid}</a></TD>{/if}
	<TD align=center style='font-size:85%'>{if $r.service}
		<a href='pop_services.php?table={$r.service}&id={$r.service_id}'>{$r.service|replace:"usage_":""}<br>{$r.service_id}</a>
	{elseif $r.bill_no}<a href="?module=newaccounts&action=bill_view&bill={$r.bill_no}" style="font-size:medium;font-weight: bold">{$r.bill_no}</a>{else}&nbsp;{/if}</TD>
{/if}
	<TD><a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'>{$r.state_name}</a>
		{if $showStages && $r.state_id!=2}<a href='javascript:toggle2("tt_main{$smarty.foreach.outer.iteration}")'>&raquo;&raquo;</a>{/if}</TD>
	<TD>{$r.user_main}</TD>
	<TD style='font-size:85%'>{$r.problem}</TD>
	<TD{if $r.is_sms_send} style="background-color: yellow;"{/if}>{if $r.state_id==2}
		{$r.date_start|udate_with_timezone}<br>
		{$r.date_edit|udate_with_timezone}
	{elseif $r.state_id==4}
			выезд {$r.date_start|udate_with_timezone}<br>
			{if $r.is_active}
				прошло <font color=red>{$r.time_pass} / {$r.time_limit}</span>
			{/if}
	{else}
		{$r.date_start|udate_with_timezone}<br>
		{if $r.is_active}
			прошло <font color=red>{$r.time_pass} / {$r.time_limit}</span>
		{else}
			неактивна / {$r.time_pass}
		{/if}
	{/if}</TD>
</TR>
{/if}
{/foreach}
</TBODY></TABLE>

{if count($pager_pages)>1}
Страницы: {foreach from=$pager_pages item=i} {if $pager_page == $i} {$i} {else} <a href='{$pager_url}&filtred=true&page={$i}'>{$i}</a>{/if} {/foreach}<br>
{/if}


{/if}
