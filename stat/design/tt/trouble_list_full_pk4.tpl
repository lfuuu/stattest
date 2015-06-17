{if !isset($hide_tts)}{if !isset($tt_wo_explain) && $tt_design=='full'}<H2>{$tt_header}
	{if $fixclient_data}(клиент <a href='{$LINK_START}/clients/view?id={$fixclient_data.client}'>{$fixclient_data.client}</a>){/if}
	</H2>
{elseif $tt_design=='client'}
	<H3><a href='{$LINK_START}module=tt&action=list&mode=1'>{$tt_header}</a></H3>
{/if}
<TABLE class={if $tt_design=='service'}insblock{else}price{/if} cellSpacing=4 cellPadding=2 width="{if $tt_design=='service'}700px{else}100%{/if}" border=0>
<TBODY>
<TR>
	{if $tt_design=='full'}
		<TD class=header vAlign=bottom>{sort_link sort=1 text='&#8470;' link=$CUR sort_cur=$sort so_cur=$so}</TD>
		{if $tt_subject<2}
			{if $tt_subject<1}<TD class=header vAlign=bottom>{sort_link sort=2 text='Клиент' link=$CUR sort_cur=$sort so_cur=$so}</TD>{/if}
			<TD class=header vAlign=bottom>Услуга</TD>

		{/if}
		<TD class=header vAlign=bottom>{sort_link sort=3 text='Этап' link=$CUR sort_cur=$sort so_cur=$so}</TD>
		<TD class=header vAlign=bottom>{sort_link sort=4 text='Ответ.' link=$CUR sort_cur=$sort so_cur=$so}</TD>
	{else}
		{if $tt_subject<2}
			{if $tt_subject<1}<TD class=header vAlign=bottom>клиент</TD>{/if}
			<TD class=header vAlign=bottom>услуга</TD>
		{/if}
		<TD class=header vAlign=bottom>этап</TD>
		<TD class=header vAlign=bottom>ответ.</TD>
	{/if}
	<TD class=header vAlign=bottom width=45%>проблема</TD>
	<TD class=header vAlign=bottom>сроки</TD>
</TR>
{foreach from=$tt_troubles item=r name=outer}
<tr>
<td colspan=7 style="background-color:{if $r.is_payed == '1'}#CCFFCC;{else}{cycle values="#E5E5E5,#F5F5F5"};{/if}" >

<table border=0 width=90%>
    <tr>
        <td rowspan=3 width=20% valign=top align=center nowrap>{$r.date_start|udate:'Y.m.d H:i'} / {$r.user_author}<br>{$r.add_info.order_given}<br>{if $r.client_orig == "nbn"}<b>{$r.add_info.req_no}</b><br>{/if}
        {if $r.service}<a href='pop_services.php?table={$r.service}&id={$r.service_id}'>{$r.service|replace:"usage_":""}<br>{$r.service_id}</a>
        {elseif $r.bill_no}<a href="?module=newaccounts&action=bill_view&bill={$r.bill_no}" style="font-size:medium;font-weight: bold">{$r.bill_no}</a>
        {else}&nbsp;{/if}<br>
            {$r.user_main} / <a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'>{$r.state_name}</a>
            <br>{if $r.add_info.logistic == 'none'}
                Логистика не установленна
                {else}
                    {$r.add_info.logistic_name}
                    {if $r.add_info.logistic == "auto" || $r.add_info.logistic == "courier"}
                        <br>м. {$r.add_info.metro_name}
                    {/if}
                {/if}
        </td>
        <td>
            <a href='/clients/view?id={$r.client_orig}'>{$r.client}{if $r.client != $r.client_orig} ({$r.client_orig}){/if}</a>
        {if $r.add_info.phone}, Телефон: {$r.add_info.phone}{/if}
        {if $r.add_info.email}, E-mail: {$r.add_info.email}{/if}
        </td>
        <!--TD rowspan=3 style='font-size:85%'>{$r.problem|escape}</TD-->
    </tr>
    <tr><td>
        {if $r.add_info.metro_id}м. {$r.add_info.metro_name}, {/if}Адрес: {$r.add_info.address}<br>
        {$r.add_info.comment1} {if $r.add_info.comment2}// {$r.add_info.comment2}{/if}
        </td></tr>
    <tr>
        <td>
            {foreach from=$r.stages item=t}
{$t.date_start} <a href='./?module=tt&action=view&id={$t.trouble_id}'>{$t.state_name}</a> {$t.user_main}/{$t.user_edit}: {$t.comment}
        {if $t.doers} {foreach from=$t.doers item=d}----><b>{$d.depart} {$d.name} ({$r.date_start}){if $r.sms} <br><span style="color: #c40000;">{$r.sms.sms_send} // {$r.sms.sms_sender}</span>{/if}</b>{/foreach}{/if}<br>
            {/foreach}
        </td>
    </tr>
    </table>
</td>
</tr>
{if false}
<TR class={if $smarty.foreach.outer.iteration%2==count($tt_troubles)%2}even{else}odd{/if}{if $r.is_important} style="background-color: #f4c0c0;"{/if}>
	{if $tt_design=='full'}<TD><a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'>{$r.trouble_id}</a></TD>{/if}
{if $tt_subject<2}
	{if $tt_subject<1}<TD><a href='/clients/view?id={$r.client}'>{$r.client}</a></TD>{/if}
	<TD align=center style='font-size:85%'>{if $r.service}
		<a href='pop_services.php?table={$r.service}&id={$r.service_id}'>{$r.service|replace:"usage_":""}<br>{$r.service_id}</a>
	{elseif $r.bill_no}<a href="?module=newaccounts&action=bill_view&bill={$r.bill_no}" style="font-size:medium;font-weight: bold">{$r.bill_no}</a>{else}&nbsp;{/if}</TD>
{/if}
	<TD><a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'>{$r.state_name}</a>
		{if $showStages && $r.state_id!=2}<a href='javascript:toggle2("tt_main{$smarty.foreach.outer.iteration}")'>&raquo;&raquo;</a>{/if}</TD>
	<TD>{$r.user_main}</TD>
	<TD style='font-size:85%'>{$r.problem}</TD>
	<TD{if $r.is_sms_send} style="background-color: yellow;"{/if}>{if $r.state_id==2}
		{$r.date_start|udate:'Y.m.d H:i'}<br>
		{$r.date_edit|udate:'Y.m.d H:i'}
	{elseif $r.state_id==4}
			выезд {$r.date_start|udate:'Y.m.d H:i'}<br>
			{if $r.is_active}
				прошло <font color=red>{$r.time_pass} / {$r.time_limit}</span>
			{/if}
	{else}
		{$r.date_start|udate:'Y.m.d H:i'}<br>
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
		<TD style='font-size:85%'>{$r2.date_edit|udate:'Y.m.d H:i'}</TD>
	</TR>
	{/foreach}
	</TBODY></TABLE></TD></TR>
{/if}
{/foreach}
</TBODY></TABLE>{/if}
