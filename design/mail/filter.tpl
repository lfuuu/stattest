<H2>Письма клиентам. <a href='{$LINK_START}&module=mail&action=view&id={$mail_id}'>Письмо &#8470;{$mail_id}</a></H2>
<H3>Добавление клиентов в очередь на отправку писем</H3>
{if count($mail_clients)}
<TABLE class=price cellSpacing=4 cellPadding=2 border=0 style='width:*' width="*">
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=client>
<input type=hidden name=id value={$mail_id}>
<input type=hidden name=module value=mail>
<input type="hidden" name="filter[status][0]" value="{$mail_filter.status.0}">
<input type="hidden" name="filter[region_for][0]" value="{$mail_filter.region_for.0}">
<input type="hidden" name="filter[manager][0]" value="{$mail_filter.manager.0}">
<input type="hidden" name="filter[bill][0]" value="{$mail_filter.bill.0}">
<input type="hidden" name="filter[bill][1]" value="{$date_from}">
<input type="hidden" name="filter[bill][2]" value="{$date_to}">
<input type="hidden" name="filter[s8800][0]" value="{$mail_filter.s8800.0}">
{foreach from=$mail_filter.regions item="r"}
	<input type="hidden" name="filter[regions][]" value="{$r}">
{/foreach}
{foreach from=$mail_filter.tarifs item="t"}
	<input type="hidden" name="filter[tarifs][]" value="{$t}">
{/foreach}

<TBODY>
<TR>
  <TD class=header vAlign=bottom>Клиент</TD>
  <TD class=header valign=bottom><input type=checkbox id='allconfirm' checked onclick='javascript:check_all()'></td>
  <TD class=header valign=bottom><input type=checkbox id='allconfirm2' checked onclick='javascript:check_all2()'></td>
  <TD>&nbsp;</TD>
  </TR>
{foreach from=$mail_clients item=r name=outer}{if $r.letter_state!="sent"}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD><input type=hidden value='{$r.client}' name='clients[{$smarty.foreach.outer.iteration}]'><a href='{$LINK_START}module=clients&id={$r.client}'>{$r.client}</a></TD>
	<TD><input type=checkbox value=1 name='flag[{$smarty.foreach.outer.iteration}]' id='flag_{$smarty.foreach.outer.iteration}'{if $r.filtered} checked{/if}></TD>
	<TD><input type=checkbox value=1 name='flag2[{$smarty.foreach.outer.iteration}]' id='flag2_{$smarty.foreach.outer.iteration}'{if $r.selected} checked{/if}></TD>
	<TD><input type=hidden value='{$r.email}' name='emails[{$smarty.foreach.outer.iteration}]'>{$r.email}</TD>
</TR>
{/if}{/foreach}
</TBODY></TABLE>
<INPUT id=submit class=button type=submit value="Добавить всех этих клиентов в список на отправку">
</FORM>
<script>
function check_all(){ldelim}
	v=form.allconfirm.checked;
{foreach from=$mail_clients item=r name=outer}{if $r.filtered && $r.letter_state!="sent"}
	form.flag_{$smarty.foreach.outer.iteration}.checked=v;
{/if}{/foreach}
{rdelim}
function check_all2(){ldelim}
	v=form.allconfirm2.checked;
{foreach from=$mail_clients item=r name=outer}{if $r.selected && $r.letter_state!="sent"}
	form.flag2_{$smarty.foreach.outer.iteration}.checked=v;
{/if}{/foreach}
{rdelim}
</script>
{/if}

<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<FORM action="?" method=post id=form2 name=form2>
<input type=hidden name=action value=client>
<input type=hidden name=id value={$mail_id}>
<input type=hidden name=module value=mail>
<input type=hidden name=ack value=1>
<tbody>
<TR><TD>Статус клиента</TD><TD>
<select name='filter[status][0]'><option value='NO'>(не фильтровать по этому полю)</option>{foreach from=$f_status item=r key=k}<option value={$k} {if $mail_filter.status.0 == $k}selected="selected"{/if}>{$r.name}</option>{/foreach}</select>
</td></tr>
<TR><TD>Менеджер</TD><TD>
<select name='filter[manager][0]'><option value='NO'>(не фильтровать по этому полю)</option>{foreach from=$f_manager item=r}<option value='{$r.user}'{if $r.user==$mail_filter.manager.0} selected="selected"{/if}>{$r.name} ({$r.user})</option>{/foreach}</select>
</td></tr>
<tr><td>Счета</TD><TD>
<select name='filter[bill][0]'><option value='NO'>(не фильтровать по этому полю)</option>
<option value='1' {if $mail_filter.bill.0 == 1}selected{/if}>любые</option>
<option value='2' {if $mail_filter.bill.0 == 2}selected{/if}>полностью неоплаченные</option>
<option value='3' {if $mail_filter.bill.0 == 3}selected{/if}>оплаченные не полностью</option>
</select>
</option></select>
с <input type=text name='date_from' id="date_from" value='{$date_from}'>
по <input type=text name='date_to' id="date_to" value='{$date_to}'>
</td></tr>
<tr><td>Услуга: 8800</TD><TD>
<select name='filter[s8800][0]'><option value='NO'>(не фильтровать по этому полю)</option>
<option value='with'{if $mail_filter.s8800.0 == 'with'} selected{/if}>с услугой</option>
<option value='without'{if $mail_filter.s8800.0 == 'without'} selected{/if}>без услуги</option>
</select>
</option></select>
</td></tr>

<tr><td>Регионы:</TD><TD>
<input onchange="show_all_regions();" type="radio" value="client" name="filter[region_for][0]" id="for_clients" {if !$mail_filter.region_for.0 || $mail_filter.region_for.0 == 'client'} checked="checked"{/if}>
<label for="for_clients">Регионы для клиентов</label>

<input onchange="show_all_regions(1);" type="radio" value="tarif" name="filter[region_for][0]" id="for_tarifs" {if $mail_filter.region_for.0 == 'tarif'} checked="checked"{/if}>
<label for="for_tarifs">Регионы для номеров</label>
</td></tr>

<tr><td>&nbsp;</TD><TD id="all_regions">

{foreach from=$f_regions item="reg"}
	<div style="float: left; margin-right: 15px;" >
	{foreach from=$reg item="r"}
		{capture name="region_`$r.id`"}
			<div>{$r.name}</div>
		{/capture}
		<div>
			<input onchange="show_regions_tarifs('{$r.id}');" id="region_{$r.id}" type="checkbox" name='filter[regions][]' value="{$r.id}" {if $r.id|in_array:$mail_filter.regions}checked="checked"{/if}>
			<label for="region_{$r.id}">{$r.name}</option>
		</div>
	{/foreach}
	</div>
{/foreach}
<div style="clear: both;"></div>
</td></tr>

<tr><td>Тарифы:</TD><TD>
{foreach from=$f_tarifs item="reg" key="k"}
{assign var="selected_region" value=false}
{if $k|in_array:$mail_filter.regions && $mail_filter.region_for.0 == 'tarif'}
	{assign var="selected_region" value=true}
{/if}
<div id="tarifs_for_{$k}" style="margin-bottom: 10px; {if !$selected_region}display:none;{/if}">
	{assign var="name" value="region_`$k`"}
	{$smarty.capture.$name}
	{foreach from=$reg item="r"}
		<div style="float: left; margin-right: 15px; ">
		{foreach from=$r item="t"}
			<div style="font-size: 11px;">
				<input {if !$selected_region}disabled="disabled"{/if} id="tarif_{$t.id}" type="checkbox" name='filter[tarifs][]' value="{$t.id}" {if $t.id|in_array:$mail_filter.tarifs}checked="checked"{/if}>
				<label for="tarif_{$t.id}">{$t.name}</option>
			</div>
		{/foreach}
		</div>
	{/foreach}
	<div style="clear: both;"></div>
</div>
{/foreach}
</td></tr>

<tr><td colspan=2>
<INPUT id=submit class=button type=submit value="Фильтр">
</td></tr>
</tbody></form></table>
<script>
	optools.DatePickerInit();
	{literal}
	function show_regions_tarifs(id)
	{
		var isTarif=$('#for_tarifs')[0].checked;
		if (isTarif) {
			$('#tarifs_for_'+id).toggle();
		}
		var isSelected=$('#region_'+id)[0].checked;
		$('#tarifs_for_'+id+' input[type=checkbox]').each(function(o,i){i.disabled = !(isSelected && isTarif);});
	}
	function show_all_regions(show) 
	{
		show = show | 0;
		var regions = $('#all_regions input[type=checkbox]');
		
		regions.each(function(o,i){
			if (i.checked)
			{
				show_regions_tarifs(i.value);
				if (!show) {
					$('#tarifs_for_'+i.value).toggle();
				}
			}
		});
		
	}
	{/literal}
</script>