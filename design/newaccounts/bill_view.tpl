<H3 style='font-size:110%;margin: 0px'>
<table border=0 width=100%>
	<tr>
		<td colspan="1">
			<a href="./?module=clients&id={$bill_client.client_orig}"><img src="images/client.jpg" title="Клиент" border=0></a>&nbsp;
			<a href='./?module=newaccounts&action=bill_list&clients_client={$bill_client.client_orig}'><img src="images/cash.png" title="Счета" border=0></a>&nbsp;
            <a href='{$LINK_START}module=newaccounts&action=bill_list&clients_client={$bill_client.client_orig}' style="font-weight: bold; font-size: large">
				{$bill_client.client}
			</a>
{assign var="isClosed" value="0"}{if $tt_trouble && $tt_trouble.state_id == 20}{assign var="isClosed" value="1"}{/if}
{*if !$isClosed*}
{if $tt_trouble.trouble_name}{$tt_trouble.trouble_name}{else}Заказ{/if}{if $bill.is_rollback}-<b><u>возврат</u></b>{/if} <b style="font-weight: bold; font-size: large">{$bill.bill_no}</b>

{if !$all4net_order_number && !$1c_bill_flag}
{if !$isClosed}<a href='{$LINK_START}module=newaccounts&action=bill_edit&bill={$bill.bill_no}'>редактировать</a> /
<a href='{$LINK_START}module=newaccounts&action=bill_delete&bill={$bill.bill_no}'>удалить</a>
 / <a href='{$LINK_START}module=newaccounts&action=bill_clear&bill={$bill.bill_no}'>очистить</a>{/if}
 {elseif $1c_bill_flag}
{if !$isClosed}<a href='{$LINK_START}module=newaccounts&action=make_1c_bill&bill_no={$bill.bill_no}'>редактировать</a> /
    <a href='{$LINK_START}module=newaccounts&action=bill_delete&bill={$bill.bill_no}'>удалить</a>{/if}
    </td>
            <td>
                {if !$bill.cleared_flag}Cчет не проведен{else}Счет проведен{/if}
                {if false && access('newaccounts_bills','edit') && !$isClosed}
                <form action="?" method="post">
                    <input type="hidden" name="module" value="newaccounts" />
                    <input type="hidden" name="action" value="bill_cleared" />
                    <input type="hidden" name="bill_no" value="{$bill.bill_no}" />
                    <input type="submit" name="ok" value="{if $bill.cleared_flag}Не проведен{else}Проведен{/if}" />
                </form>
                {/if}
    {if $bill_client.type == "multi"}<br><a href="./?module=newaccounts&action=make_1c_bill&tty=shop_orders&from_order={$bill.bill_no}"> Создать заказ на основе данных этого</a>{/if}
    {if $bill.is_payed != 1}<br><a href="./?module=newaccounts&action=pay_add&bill_no={$bill.bill_no}">Внести платеж</a>{/if}
            </td>
        </tr>
        <tr>
{if !$isClosed}
            <td>Выбрать исполнителя:
            <form method='POST'><input type='hidden' name='select_doer' value='1' /><input type='hidden' name='bill_no' value='{$bill.bill_no}' /><select name='doer'><option value='0'>Отсутствует</option>{foreach from=$doers item='doer'}<option value='{$doer.id}'>{$doer.name} - {$doer.depart}</option>{/foreach}</select><input type='submit' value='Выбрать' /></form></td>
            <td><form method='POST'><input type='hidden' name='bill_no' value='{$bill.bill_no}' />Предпологаемый тип платежа:<br> <select name="nal"><option value='---'>Не выбрано</option>
    <option value="beznal">безнал</option>
    <option value="nal">нал</option>
    <option value="prov">пров</option>
    </select><input type='submit' name='select_nal' value='Выбрать' /></form>{/if}</td>
        </tr>
    {if $bill_manager}
        <tr><td></td><td><span title="Менеджер, который провел сделку по данному счету, и получит с него бонусы.">Менеджер счета*</span>: {$bill_manager}</td></tr>
    {/if}
		{if $bill.payed_ya > 0}<tr><td>&nbsp;</td><td>

		{if $ym_pay eq 'success'}<div style="color:green;font-weight:bold;font-size: 20px;">Оплата прошла</div>
		{elseif $ym_pay neq ''}<div style="color:red;font-weight:bold;font-size: 20px;">Оплата НЕ прошла<br/>{$ym_pay}</div>{/if}

		Оплачено Yandex: <b>{$bill.payed_ya}</b></td></tr>
		{elseif $pay_to_comstar>0}
			<tr><td>&nbsp;</td><td>

		{if $ym_pay eq 'success'}<div style="color:green;font-weight:bold;font-size: 20px;">Оплата прошла</div>
		{elseif $ym_pay neq ''}<div style="color:red;font-weight:bold;font-size: 20px;">Оплата НЕ прошла<br/>{$ym_pay}</div>{/if}

			Yandex Коплате: <b>{$pay_to_comstar|round:2}</b><br>счет: {$pay_to_comstar_acc_no}
			<form method="get">
			<input type="hidden" name="module" value="yandex"/>
			<input type="hidden" name="action" value="pay_stat"/>
			<input type="hidden" name="bill" value="{$bill.bill_no}"/>
			<input type="hidden" name="comstar" value="{$pay_to_comstar_acc_no}"/>
			<input type="hidden" name="sum" value="{$pay_to_comstar|round:2}"/>
			<input type="hidden" name="backurl" value="{$pay_to_comstar_back}"/>
			<input type="submit" value="Оплатить YM">
			</form>
			</td></tr>
		{/if}
    </table>
{else}{*all4net*}
<table>
	<tr>
		<td>
    {if !$isClosed}
			Выбрать исполнителя: <form method='POST'><input type='hidden' name='select_doer' value='1' /><input type='hidden' name='bill_no' value='{$bill.bill_no}' /><select name='doer'><option value='0'>Отсутствует</option>{foreach from=$doers item='doer'}<option value='{$doer.id}'>{$doer.name} - {$doer.depart}</option>{/foreach}</select><input type='submit' value='Выбрать' /></form>

<form method='POST'><input type='hidden' name='bill_no' value='{$bill.bill_no}' />Предпологаемый тип платежа: <select name="nal"><option value='---'>Не выбрано</option>
<option value="beznal">безнал</option>
<option value="nal">нал</option>
<option value="prov">пров</option>
</select><input type='submit' name='select_nal' value='Выбрать' /></form>
    {/if}
    {/if}
 {if $all4net_order_number}<a href='http://all4net.ru/admin/orders/shop/details.html?id={$all4net_order_number}' target='_blank'>Заказ в all4net</a>
		</td>
	</tr>
</table>
{/if}</H3>


{if !$isClosed}
<table width=100%><tr><td>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=bill value="{$bill.bill_no}">
<input type=hidden name=action value="bill_comment">
<input class=text type=text value="{$bill.comment|escape}" name=comment>
<input type=submit class=button value='ок'></form>
</td><td>
    {if $bill.is_payed != 1}<br><a href="./?module=newaccounts&action=pay_add&bill_no={$bill.bill_no}">Внести платеж</a>{/if}
    </td></tr></table>
{else}{$bill.comment}
{/if}
<table>
	<tr>
		<td>Дата проводки:</td><td><b>{$bill.bill_date}</b></td>
		<td>Валюта проводки:</td><td><b{if $bill.currency=='RUR'} style='color:blue'{/if}>{$bill.currency}</b></td>
		<td>Исполнитель:</td><td>{if $bill.courier_id != 0}<i style="color: green">{$bill_courier}</i>{else}{$bill_courier|replace:"-":""}{/if}</td>
		<td>Предполагаемый тип платежа:</td><td><i{if $bill.nal != "beznal"} style="background-color: {if $bill.nal=="nal"}#ffc0c0{else}#c0c0ff{/if}"{/if}>{$bill.nal}</i></td>
	</tr>
</table>

{if $bill_comment.comment}
<br><b><i>Комментарий:</i></b><br>
Дата: {$bill_comment.date}<br>
Автор: {$bill_comment.user}<br>
Текст: {$bill_comment.comment}
{/if}
{if $store}
<br>Склад:  <b>{$store}</b>
{/if}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr class=even style='font-weight:bold'><td>&#8470;</td><td width="1%">артикул</td><td>что</td><td>период</td><td>сколько{if $cur_state && $cur_state == 17}/отгружено{/if}</td><td>цена</td><td>сумма</td>{if $bill_bonus}<td>бонус</td>{/if}<td>тип</td></tr>
{assign var="bonus_sum" value=0}
{foreach from=$bill_lines item=item key=key name=outer}
<tr class='{cycle values="odd,even"}'>
<td>{$smarty.foreach.outer.iteration}.</td>
<td align=left><span title="{$item.art|escape}">{$item.art|truncate:10}<br>

{if $item.type == "good"}
    {if $item.store == "yes"}
        <b style="color: green;">Склад</b>
    {elseif $item.store == "no"}
        <b style="color: blue;">Заказ</b>
    {elseif $item.store == "remote"}
        <b style="color: #c40000;">ДалСклад</b>
    {/if}
{/if}
</span></td>
<td>
{if $item.service && $item.service != '1C'}<a target=_blank href='{$PATH_TO_ROOT}pop_services.php?table={$item.service}&id={$item.id_service}'>{/if}
{$item.item}
{if $item.service}</a>{/if}
</td>
<td>{if access('newaccounts_bills','edit')}<a href='#' onclick='optools.bills.changeBillItemDate(event,"{$bill.bill_no}",{$item.sort});return false' style='text-decoration:none;color:#333333;'>{/if}<nobr>{$item.date_from}</nobr><br><nobr>{$item.date_to}</nobr>{if access('newaccounts_bills')}</a>{/if}</td>
<td>{$item.amount}{if $cur_state && $cur_state == 17}/<span {if $item.amount != $item.dispatch}style="font-weight: bold; color: #c40000;"{/if}>{$item.dispatch}{/if}</td>
<td align=right>{$item.price}</td>
<td align=right>{if $item.all4net_price<>0}{$item.all4net_price*$item.amount|round:2}{else}{if $bill_client.nds_zero}{$item.sum|round:2}{else}{$item.sum*1.18|round:2}{/if}{/if}</td>
{if $bill_bonus}<td align=right>{if $bill_bonus[$item.code_1c]}{$bill_bonus[$item.code_1c]}{assign var="bonus_sum" value=`$bill_bonus[$item.code_1c]+$bonus_sum`}{/if}</td>{/if}
<td>{$item.type}</td>
</tr>
{/foreach}
<tr>&nbsp;</td><td colspan=5 align=right><b>Итого: </b>&nbsp; </td><td align=right><b>{$bill.sum|round:2}</b></td>{if $bill_bonus}<td align=right><b>{$bonus_sum|round:2}</b></td>{/if}</tr>
</TABLE>

{if !$isClosed && !$all4net_order_number && !$1c_bill_flag}
<table>
	<tr>
		<td>
        <a href='{$LINK_START}module=newaccounts&action=bill_add&bill={$bill.bill_no}&obj=avans'>Аванс</a> /
        <a href='{$LINK_START}module=newaccounts&action=bill_add&bill={$bill.bill_no}&obj=deposit'>Задаток</a> /
        <a href='{$LINK_START}module=newaccounts&action=bill_add&bill={$bill.bill_no}&obj=deposit_back'>возврат</a> /
        <a href='{$LINK_START}module=newaccounts&action=bill_add&bill={$bill.bill_no}&obj=deposit_sub'>вычет</a></td>
		<td>Услуги со статусом connecting: <a href='{$LINK_START}module=newaccounts&action=bill_add&bill={$bill.bill_no}&obj=connecting'>всё</a> <a href='{$LINK_START}module=newaccounts&action=bill_add&bill={$bill.bill_no}&obj=connecting_ab'>только абонентку</a></td>
		<td><a href='{$LINK_START}module=newaccounts&action=bill_add&bill={$bill.bill_no}&obj=regular'>Ежемесячное</a></td>
	</tr>
</table>

<FORM action="?" method=get id=form2 name=form2 style='padding-top:6px;padding-bottom:0px;margin:0 0 0 0'>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=bill value="{$bill.bill_no}">
<input type=hidden name=action value=bill_add>
<input type=hidden name=obj value=template>
<select name=tbill class=text>
{foreach from=$template_bills item=item name=outer}
<option value="{$item.bill_no}">{$item.comment}</option>
{/foreach}
</select><input type=submit class=button value="добавить">
</form>
{/if}

<hr>

<form action='?' method=get target=_blank name=formsend id=formsend>
<table cellspacing=0 cellpadding=10 valign=top><tr>

<td style='border-width:1 0 1 1; border-style:solid;border-color:#808080' valign=top>
<b>Печать/отправка:</b><br>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=bill value="{$bill.bill_no}">
{if $bill.currency=='USD'}
<input type=checkbox value=1 name="bill-2-USD" id=cb1><label for=cb1>Счет в USD (предоплата)</label><br>
{*<input type=checkbox value=1 name="bill-1-USD" id=cb2><label for=cb2>Счет в USD</label><br>*}
{/if}
<input type=checkbox value=1 name="bill-2-RUR" id=cb3><label for=cb3>Счет в RUR (предоплата)</label><br>
{*<input type=checkbox value=1 name="bill-1-RUR" id=cb4><label for=cb4>Счет в RUR</label><br>*}
<input type=checkbox value=1 name="envelope" id=cb4c><label for=cb4c{if $client.mail_print =="no"} style="text-decoration: line-through;"{/if}>Сопроводительное письмо</label><br>

<input type=checkbox value=1 name="invoice-1" id=cb5><label for=cb5{if !$bill_invoices[1]} style='color:#C0C0C0'{/if}>Счёт-фактура (1)</label><br>
<input type=checkbox value=1 name="invoice-2" id=cb6><label for=cb6{if !$bill_invoices[2]} style='color:#C0C0C0'{/if}>Счёт-фактура (2)</label><br>
<input type=checkbox value=1 name="invoice-3" id=cb7><label for=cb7{if !$bill_invoices[3] || $deinv3} style='color:#C0C0C0'{/if}>Счёт-фактура (3)</label><br>
<input type=checkbox value=1 name="invoice-4" id=cbc><label for=cbc{if $bill_invoices[5] eq 0} style='color:#C0C0C0'{elseif $bill_invoices[5] eq -1} style='background-color:#ffc0c0;font-style: italic;'{/if}>Счёт-фактура (4)</label><br>
<input type=checkbox value=1 name="invoice-5" id=cb10><label for=cb10{if !$bill_invoices[6]} style='color:#C0C0C0'{/if}>Счёт-фактура (5)</label><br>

<input type=checkbox value=1 name="upd-1" id="upd1"><label for="upd1"{if !$bill_upd[1]} style='color:#C0C0C0'{/if}>УПД (1)</label><br>
<input type=checkbox value=1 name="upd-2" id="upd2"><label for="upd2"{if !$bill_upd[2]} style='color:#C0C0C0'{/if}>УПД (2)</label><br>

Действие: <select name=action id="action">
<option value="bill_mprint">печать</option>
<option value="bill_email">отправка</option>
</select><br>

<br><input type=button class=button value='Поехали' onclick="doFormSend()">




</td><td valign=top style='border-width:1 1 1 0; border-style:solid;border-color:#808080'>

<input type=checkbox value=1 name="akt-1" id=cb8><label for=cb8{if !$bill_invoices[1]} style='color:#C0C0C0'{/if}>Акт (1)</label><br>
<input type=checkbox value=1 name="akt-2" id=cb9><label for=cb9{if !$bill_invoices[2]} style='color:#C0C0C0'{/if}>Акт (2)</label><br>
<input type=checkbox value=1 name="akt-3" id=cba><label for=cba{if !$bill_akts[3]} style='color:#C0C0C0'{/if}>Акт (3)</label><br>
<input type=checkbox value=1 name="lading" id=cbb><label for=cbb{if !$bill_invoices[4]} style='color:#C0C0C0'{/if}>Накладная</label><br>
<input type="checkbox" value="1" name="gds" id="gds" /><label for=gds{if !$bill_invoices[7]} style='color:#C0C0C0'{/if}>Товарный чек</label><br>
<input type="checkbox" value="1" name="gds-serial" id="gds_serial" /><label for=gds_serial{if !$bill_invoices[7]} style='color:#C0C0C0'{/if}>Товарный чек (с серийными номерами)</label><br>
<input type="checkbox" value="1" name="gds-2" id="cbd" /><label for=cbd style='color:#808080'>Товарный чек (все позиции)</label><hr>
{if $is_set_date}
<input type='text' value='{if $bill.doc_ts}{$bill.doc_ts|date_format:"%d.%m.%Y"}{else}{$smarty.now|date_format:"%d.%m.%Y"}{/if}' name='without_date_date' size='10'{if $bill.doc_ts} style="color: #c40000; font-weight: bold;"{/if}> <br><input type='checkbox' name='without_date' value='1' id='wd' /><label for=wd>Установить дату документа</label><br>
<hr />
{/if}
{if $bill_client.client_orig == "nbn"}
<input type=checkbox value=1 name="nbn_deliv" id=wm9><label for='wm9'>NetByNet: акт доставка</label><br>
<input type=checkbox value=1 name="nbn_modem" id=wm10><label for='wm10'>NetByNet: акт модем</label><br>
<input type=checkbox value=1 name="nbn_gds" id=wm11><label for='wm11'>NetByNet: заказ</label><br>
{/if}


</td><td valign=top>
Почтовый реестр: {$bill.postreg}
<br>
<a href='{$LINK_START}module=newaccounts&action=bill_postreg&bill={$bill.bill_no}'>зарегистрировать</a><br>
<a href='{$LINK_START}module=newaccounts&action=bill_postreg&option=1&bill={$bill.bill_no}'>удалить</a><br>
<br><br>
Счёт-фактура (2): {if $bill.inv2to1==1}<a href='{$LINK_START}module=newaccounts&action=bill_generate&obj=inv2to1&bill={$bill.bill_no}&inv2to1=0'>как обычно</a>{else}как обычно{/if} / {if $bill.inv2to1==0}<a href='{$LINK_START}module=newaccounts&action=bill_generate&obj=inv2to1&bill={$bill.bill_no}&inv2to1=1'>по дате первой</a>{else}по дате первой{/if}<br>
</td></tr></table>

</form>
<script>
{literal}
function doFormSend()
{
	$("#formsend").submit();
	return true;
	if($("#formsend select[name=action]").val() == "bill_email")
	{
		var s ="";
		$("#formsend input[type=hidden]").each(function(o,i){var a = $(i); s += "&"+a.attr("name")+"="+a.val()});

		$("#formsend input:checked").each(function(o,i){var a = $(i); s += "&"+a.attr("name")+"="+a.val()});
		$("#formsend select").each(function(o,i){var a = $(i); s += "&"+a.attr("name")+"="+a.val()});
		$("#formsend input[type=text]").each(function(o,i){var a = $(i); s += "&"+a.attr("name")+"="+escape(a.val())});

		window.open("./?"+s, "_blank", "width=1000,height=600");
	}else{
		$("#formsend").submit();
	}
}
{/literal}
</script>

{if $bill.currency=='USD'}
<table cellspacing=0 cellpadding=10 valign=top border=0 style='border-style:solid;border-color:#808080;border-width:1;margin-top:10px'>
<tr><td style='padding-top:0px' valign=top><h3>Сформированные:</h3><br>
{if $bill.inv_rur!=0}Счёт-фактура и акт (1,2,3) {$bill.inv_rur} р, {$bill.inv1_date}<br>{else}
	{if $bill.inv1_rate!=0}Счёт-фактура и акт (1) по курсу {$bill.inv1_rate}, {$bill.inv1_date}<br>{/if}
	{if $bill.inv2_rate!=0}Счёт-фактура и акт (2) по курсу {$bill.inv2_rate}, {$bill.inv2_date}<br>{/if}
	{if $bill.inv3_rate!=0}Счёт-фактура и акт (3) по курсу {$bill.inv3_rate}, {$bill.inv3_date}<br>{/if}
{/if}

{if $bill.gen_bill_rur!=0 || $bill.gen_bill_rate!=0}Счёт {if $bill.gen_bill_rur!=0}= {$bill.gen_bill_rur} р{else}по курсу {$bill.gen_bill_rate}{/if}, {$bill.gen_bill_date}<br>{/if}
</td><td style='padding-top:0px;border:0 solid #808080;border-left-width:1' valign=top>
<h3>Формирование:</h3><br>
По сумме:<br>
<form style='display:inline' name=formgen id=formgen>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=bill_generate>
<input type=hidden name=bill value="{$bill.bill_no}">
<input type=hidden name=type value=vsum>
<input type=hidden id=obj name=obj value=''>
<input type=text name=sum class=text value='{$bgen_psum[0]}'>
<input type=button class=button value='Счёт-фактура и акт (1,2,3)' onclick="formgen['obj'].value='invoice'; formgen.submit(); ">
<input type=button class=button value='Счет' onclick="formgen['obj'].value='bill'; formgen.submit(); ">
</form><br>
<br>
По курсу:<br>
<form style='display:inline'><input type=hidden name=module value=newaccounts><input type=hidden name=action value=bill_generate>
<input type=hidden name=type value=rate>
<input type=hidden name=bill value="{$bill.bill_no}">
<input type=hidden name=obj value='invoice'>
<input type=hidden name=inv_num value='1'>
<input type=text name=rate class=text value='{$bgen_rate.0[1]}'>
<input type=submit class=button value='Счёт-фактура и акт (1)'>
</form><br>
<form style='display:inline'><input type=hidden name=module value=newaccounts><input type=hidden name=action value=bill_generate>
<input type=hidden name=type value=rate>
<input type=hidden name=bill value="{$bill.bill_no}">
<input type=hidden name=obj value='invoice'>
<input type=hidden name=inv_num value='2'>
<input type=text name=rate class=text value='{$bgen_rate.1[1]}'>
<input type=submit class=button value='Счёт-фактура и акт (2)'>
</form><br>
<form style='display:inline'><input type=hidden name=module value=newaccounts><input type=hidden name=action value=bill_generate>
<input type=hidden name=type value=rate>
<input type=hidden name=bill value="{$bill.bill_no}">
<input type=hidden name=obj value='invoice'>
<input type=hidden name=inv_num value='3'>
<input type=text name=rate class=text value='{$bgen_rate.2[1]}'>
<input type=submit class=button value='Счёт-фактура и акт (3)'>
</form><br>
<form style='display:inline'><input type=hidden name=module value=newaccounts><input type=hidden name=action value=bill_generate>
<input type=hidden name=type value=rate>
<input type=hidden name=bill value="{$bill.bill_no}">
<input type=hidden name=obj value='bill'>
<input type=text name=rate class=text value='{$bgen_rate.3[1]}'>
<input type=submit class=button value='Счет'>
</form><br>
</td></tr></table>
{/if}

<h3>История изменений счёта:</h3>
{if count($bill_history)}{foreach from=$bill_history item=L key=key name=outer}
<b>{$L.ts} - {$L.user}</b>: {$L.comment}<br>
{/foreach}{/if}
<table style='display:none;position:absolute;background-color:silver;border:double;' id='ItemsDatesTable'>
	<tr>
		<td><input type='text' id='billItemDateTable_date_from' size='10' /></td>
		<td><input type='text' id='billItemDateTable_date_to' size='10' /></td>
	</tr>
	<tr><td colspan='2' align='center'>
		<input type='button' value='Ok' onclick='optools.bills.fixItemDate();' />
	</td></tr>
</table>

{*if $tt_trouble.doer_id}
<a name="doer_comment"></a>
<form style='display:inline'><input type=hidden name=module value=newaccounts><input type=hidden name=action value=bill_courier_comment>
<h3>Коментарии курьеру:</h3>
<textarea name="comment" style="width: 75%;height: 60px;">{$tt_trouble.doer_comment}</textarea>
<input type=hidden name=bill value="{$bill.bill_no}"><br>
<input type=hidden name=doer_id value="{$tt_trouble.doer_id}"><br>
<input type=submit class=button value='Сохранить'>
</form><br>
{/if*}
