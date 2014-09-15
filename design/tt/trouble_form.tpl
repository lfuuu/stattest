{if $fixclient}
<div id="trouble_to_add"{if $tt_show_add} style="display:none;"{/if}><div onclick="$('#trouble_to_add').toggle();$('#trouble_add').toggle();" style="cursor: pointer;"><img border="0" src="./images/icons/add.gif"><u>Добавить заявку</u></div></div>
<div id="trouble_add"{if !$tt_show_add} style="display:none;"{/if}>
<div onclick="$('#trouble_add').toggle();$('#trouble_to_add').toggle();" style="cursor: pointer;"><img border="0" src="./images/icons/add.gif"><u>Добавить заявку (спрятать)</u></div>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=add>	
<input type=hidden name=module value=tt>
{if $curtype}
	<input type="hidden" name="type" value="{$curtype.code}" />
{/if}
<TABLE class=mform cellSpacing=4 cellPadding=2 border=0>
{if !$curtype}<TR>
	<TD class=left width=30%>Тип заявки</TD>
	<TD><SELECT name=type class=text style='width:300px' onclick = 'if (this.selectedIndex==null) return; eval("tt_"+this.options[this.selectedIndex].getAttribute("value")+"()");'>
		{foreach from=$ttypes item='t'}
		<option value="{$t.code}">{$t.name}</option>
		{/foreach}
	</SELECT></TD>
</TR>{/if}<TR>
	<TD class=left>Клиент</TD>
	<TD><input name=client readonly="readonly" value='{if $fixclient}{$fixclient_data.client}{/if}' class=text style='width:300px'></TD>
</TR>{if $tt_service}<TR>	
	<TD class=left>Услуга</TD>
	<TD><a href='pop_services.php?table={$tt_service}&id={$tt_service_id}'>{$tt_service} #{$tt_service_id}</a></TD>
	<input type=hidden name=service value='{$tt_service}'>
	<input type=hidden name=service_id value='{$tt_service_id}'>
</TR>{/if}<TR id=dt_C1 style='display:none'>
	<TD class=left id=dt_C1_capt>Показывать с</TD>
	<TD><input type=textbox id=date_start name=date_start value="{0|mdate:'Y-m-d H:i:s'}" class=text style='width:300px'></TD>
</TR><TR id=dt_A1>
	<TD class=left>Время на устранение</TD>
	<TD><input type=radio id=radiostart1 name=A checked onclick='start1.disabled=false; start2.disabled=true'><input id=start1 type=textbox name=time value=1 class=text style='text-align:right;width:180px'> час</TD>
</TR><TR id=dt_A2>
	<TD class=left>Дата желаемого окончания</TD>
	<TD><input type=radio id=radiostart2 name=A onclick='start1.disabled=true; start2.disabled=false'><input id=start2 disabled type=textbox name=date_finish_desired value="{0|mdate:'Y-m-d H:i:s'}" class=text style='width:180px'></TD>
</TR>
<tr>
<td class=left>Тип заявки:</td>
<td>{html_options options=$trouble_subtypes name="trouble_subtype"}</td>
</tr>

<tr id="bills_list" style="display: none">
	<td>Заказ/Счет</td>
	<td>
		<select name="bill_no">
			<option value="null"></option>
			{foreach from=$bills item='b' key='n'}
			<option value="{$n}">{$n}</option>
			{/foreach}
		</select>
	</td>
</tr><TR><TD colspan=2>
Текст проблемы:<br><textarea name=problem class=textarea></textarea>
</TD></TR><TR>
	<TD class=left>Ответственный</TD>
	<TD>
		  <SELECT name=user>{foreach from=$tt_users item=item}
                {if $item.user}
                    <option value='{$item.user}'{if $authuser.user==$item.user} selected{/if}>{$item.name} ({$item.user})</option>
                {else}
                    </optgroup>
                    <optgroup label="{$item.name}">

                {/if}
{/foreach}</optgroup></select>
    </TD>
</TR>
<tr>
<td class=left>Важная заявка</td>
<td><input type=checkbox name="is_important" value=1></td>
</tr>
<TR>
	<TD colspan=2><INPUT id=submit class=button type=submit value="Завести заявку"></TD>
</TR>
</TABLE>
</form>
</div>
<script language=javascript>{literal}
function tt_trouble(){
	dt_C1.style.display="none";		//дата начала			date_start
	dt_A1.style.display="";			//время на устранение	date_finish_desired
	dt_A2.style.display="";			//дата жел. окончания	date_finish_desired
	//dt_B1.style.display="none";		//расписание
	document.getElementById('bills_list').style.display = "none"
	form.radiostart2.style.display='';
	form.radiostart1.checked=true;
	form.start2.disabled=form.radiostart1.checked;
	form.start1.disabled=!form.start2.disabled;
	form.radiostart2.checked=form.start1.disabled;
}
function tt_task(){
	dt_C1.style.display="";
	dt_C1_capt.innerHTML="Показывать с";
	dt_A1.style.display="none";
	dt_A2.style.display="";
	//dt_B1.style.display="none";
	document.getElementById('bills_list').style.display = "none"
	form.radiostart2.style.display='none';
	form.radiostart1.checked=false;
	form.start2.disabled=form.radiostart1.checked;
	form.start1.disabled=!form.start2.disabled;
	form.radiostart2.checked=form.start1.disabled;
}
function tt_support_welltime(){
	tt_trouble();
}
function tt_shop_orders(){
	dt_C1.style.display="none"
	dt_A1.style.display="none"
	dt_A2.style.display="none"
	//dt_B1.style.display="none"
	document.getElementById('bills_list').style.display = "block"
}
function tt_mounting_orders(){
	tt_shop_orders()
}
function tt_orders_kp(){
	tt_shop_orders()
}
function tt_out(){
	dt_C1.style.display="";
	dt_C1_capt.innerHTML="Дата выезда";
	dt_A1.style.display="";
	dt_A2.style.display="none";
	//dt_B1.style.display="";
}
{/literal}
{if $curtype}tt_{$curtype.code}(){/if}
</script>
{/if}
