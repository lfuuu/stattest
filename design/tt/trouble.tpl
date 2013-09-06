{if !$tt_trouble.bill_no}<H2><a href='{$LINK_START}module=tt&action=list&mode=1&clients_client={$tt_client.client}'>Заявки</a></H2>{/if}
<H3>Заявка {$tt_trouble.type}{$tt_trouble.id}{if $tt_trouble.bill_no} <span style='font-size:11px'>{mformat param=$tt_trouble.date_creation format='Y.m.d H:i:s'}</span>{/if}</H3>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
{if !$tt_trouble.bill_no}<TR>
	<TD class=left width="30%">Клиент</TD>
	<TD width="70%"><a href='{$LINK_START}module=clients&id={$tt_client.client}'>{$tt_client.company}</a> ({$tt_client.client})</TD>
</TR>
{if $tt_trouble.service}<TR>
	<TD class=left>Услуга</TD>
	<TD><a href='pop_services.php?table={$tt_trouble.service}&id={$tt_trouble.service_id}'>{$tt_trouble.service} #{$tt_trouble.service_id}</a></TD>
</TR>{/if}
{if $tt_trouble.bill_no}<tr>
	<td class="left">Заказ</td>
	<td><a href="index.php?module=newaccounts&action=bill_view&bill={$tt_trouble.bill_no}">{$tt_trouble.bill_no}</a></td>
</tr>{/if}
<TR>
	<TD class=left>Трабл создал</TD>
	<TD>{$tt_trouble.user_author_name} ({$tt_trouble.user_author}), <span style='font-size:11px'>{mformat param=$tt_trouble.date_creation format='Y.m.d H:i:s'}</span></TD>
</TR>
<TR>
	<TD class=left>Текущие сроки</TD>
	<TD>
		с {mformat param=$tt_trouble.date_start format='Y.m.d H:i:s'} по {mformat param=$tt_trouble.date_finish_desired format='Y.m.d H:i:s'}<br>
		{if $tt_trouble.is_active}
			прошло <font color=red>{$tt_trouble.time_pass} / {$tt_trouble.time_limit}</span>
		{else}
			неактивна / {$tt_trouble.time_limit}
		{/if}
	</TD>
</TR>{/if}
{if $tt_trouble.trouble_subtype}
<tr>
    <td class=left>Тип заявки: </td>
    <td>{$tt_trouble.trouble_subtype}</td>
</tr>
{/if}
<TR>
	<TD class=left width="30%">Проблема</TD>
	<TD width="70%" style='padding:5 5 5 5;border:1 solid black;height:10px; background:white;vertical-align:top;font-size:8pt'>{$tt_trouble.problem|replace:"\\n":"\n"|replace:"\\r":""|replace:"\n\n":"\n"|replace:"\n\n":"\n"|replace:"\n":"<br>"}</textarea></TD>
</TR>
{if access('tt','time') && $tt_write && $tt_trouble.state_id != 20 && $tt_trouble.state_id != 39}
<TR>
	<TD class=left>Добавить времени (часов)</TD>
	<TD><form action='?' style='padding:0 0 0 0; margin:0 0 0 0' method=post><input type=hidden name=module value=tt><input type=hidden name=action value=time><input type=hidden name=id value={$tt_trouble.id}><input type=text class=text name=time value='1'> <input type=submit class=button value='Добавить'> (введите отрицательное число, чтобы отнять время)</form></TD>
</TR>
{/if}
            {if access('tt','time') && $tt_write && $tt_trouble.state_id != 20 && $tt_trouble.state_id != 39}
            <tr>
                <td class=left title="С какого момента показывать">Дата активации </td>
                <td><form action='?' style='padding:0 0 0 0; margin:0 0 0 0' method=post><input type=hidden name=module value=tt><input type=hidden name=action value=time><input type=hidden name=id value={$tt_trouble.id}><input type=text name=date_activation value="{$tt_trouble.date_start}"> <input type=submit class=button value='Установить'></form></td>
            </tr>
            {/if}
</TBODY></TABLE>
<br>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR>
	<TD class=header vAlign=bottom width="9%">Состояние</TD>
	<TD class=header vAlign=bottom width="8%">Ответственный</TD>
	<TD class=header vAlign=bottom width="10%">сроки</TD>
	<TD class=header vAlign=bottom width="8%">Этап закрыл</TD>
	<TD class=header vAlign=bottom width="*">с комментарием</TD>
	<TD class=header vAlign=bottom width="15%">время закрытия</TD>
	</TR>
{foreach from=$tt_trouble.stages item=item name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==count($tt_trouble.stages)%2}even{else}odd{/if}>
	<TD>{$item.state_name}</TD>
	<TD>{$item.user_main}</TD>
	<TD>{$item.date_start|mdate:'m-d H:i'}<br>{$item.date_finish_desired|mdate:'m-d H:i'}</TD>
	<TD>{$item.user_edit}</TD>
	<TD>{if count($item.doers)>0}
		<table border='0' width='100%'>
			<tr>
				<td width='50%'>&nbsp;{/if}{$item.comment}{if $item.uspd}<br>{$item.uspd}{/if}{if count($item.doers)>0}</td>
				<td width='50%'><table border='0' align='right' style='background-color:lightblue'>
					<tr align='center'><td colspan='2'>Исполнители:</td></tr>
					{foreach from=$item.doers item='doer'}<tr align='center'><td>{$doer.depart}</td><td>{$doer.name}</td></tr>{/foreach}
				</table></td>
			</tr>
		</table>
	{/if}
    {if $item.doer_stages}
    <table border=0 colspan=0 rowspan=0>
        {foreach from=$item.doer_stages item=ds}<tr><td>{$ds.date}</td><td>{$ds.status_text}({$ds.status})</td><td>{$ds.comment}</td></tr>{/foreach}
    </table>
    {/if}
{if $item.rating > 0}
<br>
Оценка: {$item.user_rating}: <b>{$item.rating}</b>
{/if}
    </TD>
	<TD>{$item.date_edit}</TD>
</TR>
{/foreach}
</TBODY></TABLE>

{if ($tt_write || $tt_doComment) && $tt_trouble.state_id != 20 && $tt_trouble.state_id != 39}{*не закрыт*}
<form action="index_lite.php" method="post" id="state_1c_form">
	<input type="hidden" name="module" value="tt" />
	<input type="hidden" name="action" value="rpc_setState1c" />
	<input type=hidden name="id" value='{$tt_trouble.id}' />
	<input type="hidden" id="state_1c_form_bill_no" name="bill_no" value="{$tt_trouble.bill_no}" />
	<input type="hidden" id="state_1c_form_state" name="state" value="" />
</form>
<h3>Этап</h3>
<FORM action="./?" method=post id=form name=form>
<input type=hidden name=action value=move>
<input type=hidden name=module value=tt>
<input type=hidden name=id value='{$tt_trouble.id}'>
	    <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
	      <TBODY>
	      <TR>
	        <TR><TD class=left>Комментарий:</TD><TD>
	        <textarea name=comment class=textarea>{$stage.comment}</textarea>
	        </TD></TR>

{if $tt_write}
	        <TR><TD class=left>Новый ответственный:</TD><TD>
	        {if $admin_order && $order_editor != "stat"}
		        {foreach from=$tt_users item=item}
		        	{if $tt_trouble.user_main==$item.user}
		        		<input type=hidden name=user value={$item.user}>{$item.name} ({$item.user})
		        	{/if}
		        {/foreach}
		    {else}

		  <SELECT name=user>{foreach from=$tt_users item=item}
                {if $item.user}
                    <option value='{$item.user}'{if $tt_trouble.user_main==$item.user} selected{/if}>{$item.name} ({$item.user})</option>
                {else}
                    </optgroup>
                    <optgroup label="{$item.name}">

                {/if}
{/foreach}</optgroup></select>
			{/if} {*admin_order:end*}
	        </TD></TR>
{if $tt_trouble.is_important}
	        <TR><TD class=left style="color: #c40000;"><b>Важная заявка</b></TD><TD>
	        </TD></TR>
{/if}
	        <TR><TD class=left>Новое состояние:</TD><TD>

{if $admin_order && $order_editor != "stat"}

	{foreach from=$tt_states item=item}
		{if $tt_trouble.state_id==$item.id}{$item.name}
			<input type=hidden name='state' value='{$item.id}'>
		{/if}
	{/foreach}

{else}
			<SELECT name='state' onclick="tuspd.style.display=(document.getElementById('state_3') && state_3.selected?'':'none');">
			{foreach from=$tt_states item=item}
			{if !isset($tt_restrict_states) || !($item.pk & $tt_restrict_states)}
				<option id='state_{$item.id}' value='{$item.id}'{if $tt_trouble.state_id==$item.id} selected{/if}>{$item.name}</option>
			{/if}
			{/foreach}</select>
			{if $admin_order}
				<input type=submit value="Предать в admin.markomnet" name="to_admin" class=button>
			{/if}
{/if}
			{if $tt_trouble.state_id == 2 && access('tt','rating')}
            &nbsp; Оценка: <select name=trouble_rating>
                <option value=0>-----</option>
                <option value=1>1</option>
                <option value=2>2</option>
                <option value=3>3</option>
                <option value=4>4</option>
                <option value=5>5</option>
            </select>

            {/if}
			</TD></TR>
			{if $bill}
			<tr>
				<td class="left">Статус заказа в 1С: </td>
				<td>
					<b>{$bill.state_1c}</b>{if $tt_1c_states}&nbsp;&nbsp;&nbsp;&nbsp;
					{foreach from=$tt_1c_states item='s'}
						<input type="button" value="{$s}" onclick="statlib.modules.tt.mktt.setState1c(event,this)" />
					{/foreach}
					{/if}
					{*if $bill.state_1c == 'Новый'}<input type="button" value="Зарезервировать" onclick="statlib.modules.tt.mktt.setState1c(event,this)" /><input type="button" onclick="statlib.modules.tt.mktt.setState1c(event,this)" value="Отказ" />
					{elseif $bill.state_1c == 'Резерв'}<input type="button" onclick="statlib.modules.tt.mktt.setState1c(event,this)" value="Отменить резерв" /><input type="button" onclick="statlib.modules.tt.mktt.setState1c(event,this)" value="К отгрузке" /><input type="button" onclick="statlib.modules.tt.mktt.setState1c(event,this)" value="Отказ" />
					{elseif $bill.state_1c == 'Отгрузка'}<input type="button" value="Снять отгрузку" onclick="statlib.modules.tt.mktt.setState1c(event,this)" /><input type="button" onclick="statlib.modules.tt.mktt.setState1c(event,this)" value="Отказ" />
					{elseif $bill.state_1c == 'Самовывоз' || $bill.state_1c == 'Доставка'}<input type="button" onclick="statlib.modules.tt.mktt.setState1c(event,this)" value="Закрыть" />
					{/if*}
				</td>
			</tr>{/if}
	        <TR id=tuspd style='display:none'><TD class=left>Номер заявки в УСПД:</TD><TD>
		  <input type=text class=text name=uspd value="">
	        </TD></TR>
	        {*<TR id=tout{if $tt_trouble.state_id!=4} style='display:none'{/if}><TD class=left>Новая дата выезда:</TD><TD>
		  <input type=text class=text name=date_start style='width:200px' value="{0|mdate:'Y-m-d H:i:s'}">
	        </TD></TR>*}
	        {if !$admin_order || $order_editor == "stat"}
			<tr><td class="left">Выбрать исполнителя</td>
				<td><input type="checkbox" name="showTimeTable"{if isset($timetableShow)} checked='checked'{/if}
						onclick="if(timetable_pane.style.display=='none')timetable_pane.style.display='block';else timetable_pane.style.display='none'" /></td></tr>
			{/if}
{/if}{* <-- if $tt_write*}
	        <tr><td colspan="2" class="left">&nbsp</td></tr></TBODY></TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Добавить"></DIV>
{/if}
