<link rel="stylesheet"  href="css/themes/smoothness/jquery.ui.all.css" type="text/css"/>
<script src="js/jquery-ui-1.9.2.custom.min.js"></script>

<!--script src="js/ui/jquery.ui.datepicker.js"></script-->
<!--script src="js/ui/i18n/jquery.ui.datepicker-ru.koi8r.js"></script-->
<script>
{literal}

function set_credit_flag(){
    if ($('#credit_flag').attr('checked')) {
        if ($('#credit_size')[0].value < 0)
            $('#credit_size')[0].value = '0';
        $('#credit_size_block').show()

    } else {
        $('#credit_size')[0].value = '-1';
        $('#credit_size_block').hide();
    }
}

$(function(){
	$("#deferred").click(function()
	{
        if($(this).attr("checked"))
        {
            $("#span_deferred_date").css("display", "");
        }else{
            $("#span_deferred_date").css("display", "none");
        }

    });


    $("img.history_open").button().click(function()
    {
        $("#history_dialog").dialog(
            {
            	width: 850,
            	height: 400,
                open: function(event, ui){
                    $(this).load("./?module=clients&id={/literal}{$client.id}{literal}&action=view_history&view_only=true");
                }
            });
    }
    );

    set_credit_flag();
});

{/literal}
	var vPeriod1 = {$history_flags.m.1.v};
	var vPeriod2 = {$history_flags.m.2.v};
	var vPeriod3 = {$history_flags.m.3.v};
	var vPeriod4 = {$history_flags.m.4.v};

	var nPeriod1 = '{$history_flags.m.1.n}';
	var nPeriod2 = '{$history_flags.m.2.n}';
	var nPeriod3 = '{$history_flags.m.3.n}';
	var nPeriod4 = '{$history_flags.m.4.n}';


	{literal}

	function doMainFormSubmit()
	{
		if(statlib.modules.clients.create.checkTIK())
		{
			if($("#deferred").is(":checked"))
			{
				var periodSelected = $("input:radio[name=deferred_date]:checked").val();
				if(periodSelected == 4) // future
				{
					if(vPeriod4)
					{

						$("#dialog_deferred_future").dialog({
			            	width: 620,
			            	height: 250,
			            	buttons: {
			            		"Да": function(){
			            			$( "#form" ).submit();
			            		},
			            		"Нет": function(){
			            			$( this ).dialog( "close" );
			            		}
			            	}
		            	});
		            }else{
		            	$( "#form" ).submit();
		            }
				}else{ // past

					if(periodSelected == 1)
					{
						fPast = vPeriod1;
						nPast = nPeriod1;
					}else if(periodSelected == 2){
						fPast = vPeriod2;
						nPast = nPeriod2;
					}else if(periodSelected == 3){
						fPast = vPeriod3;
						nPast = nPeriod3;
					}else if(periodSelected == 4){
						fPast = vPeriod4;
						nPast = nPeriod4;
					}


					if(fPast)
					{
						$("#dialog_deferred_past").dialog({
			            	width: 600,
			            	height: 270,
			            	open: function() {
			            		$("#dialog_deferred_past #past").text(nPast);
			            		},
			            	buttons: {
			            		"Да": function(){
			            			$( "#form" ).submit();
			            		},
			            		"Нет": function(){
			            			$( this ).dialog( "close" );
			            		}
			            	}
		            	});
		            }else{
		            	$( "#form" ).submit();
		            }
				}
			}else{
				if(vPeriod4)
				{
					$("#dialog_deferred_future").dialog({
		            	width: 620,
		            	height: 250,
		            	buttons: {
		            		"Да": function(){
		            			$( "#form" ).submit();
		            		},
		            		"Нет": function(){
		            			$( this ).dialog( "close" );
		            		}
		            	}
	            	});
	            }else{
	            	$( "#form" ).submit();
	            }
			}
		}
	}


{/literal}
</script>



<H2>Клиенты</H2>
<H3>{if isset($mode_new)}Новый клиент{else}Редактирование клиента {$client.client} (id={$client.id}){/if}</H3>
<FORM action="?" method=post id=form name=form>
<input style='width:100%' type=hidden name=module value=clients>
{if isset($mode_new)}
<input style='width:100%' type=hidden name=action value=create>
{else}
<input style='width:100%' type=hidden name=action value={if isset($form_action)}{$form_action}{else}apply{/if}>
<input style='width:100%' type=hidden name=id value='{$client.id}'>
{/if}
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0><TBODY>
{if isset($mode_new) || (!$client.client && ($client.status=='testing' || $client.status=='negotiations'))}
	<TR><TD class=left width=30%>Код клиента:</TD><TD><input style='width:50%' name=client class=text value='{$client.client}'>
	<a href='#' onclick='form.client.value="id{if $client.id}{$client.id}{else}NNNN{/if}";'>присвоить код {if $client.id}id{$client.id}{/if}</a>
	{if !$client.id} (idNNNN, определится позднее){/if}
	</TD></TR>
{else}
	<input type=hidden name=client value='{$client.client}'>
	{if access('clients','new')}<tr>
		<td align="right" colspan="2"><a href="?module=clients&action=mkcontract">Создать новый "договор"</a></td>
	</tr><tr>
		<td align="right">Привязать к истории</td>
		<td>
			<select name="previous_reincarnation">
				<option value="0"></option>
				{foreach from=$all_cls item='c'}<option value="{$c.id}"{if $c.id==$client.previous_reincarnation} selected='selected'{/if}>{$c.client}</option>{/foreach}
			</select>
		</td>
	</tr>{/if}{if access('clients','moveUsages')}<tr>
		<td align="right">Забрать услуги</td>
		<td>
			<select name="move_usages">
				<option value=""></option>
				{foreach from=$all_cls item='c'}{if $client.client<>$c.client}<option value="{$c.id}">{$c.client}{/if}</option>{/foreach}
			</select>
		</td>
	</tr>{/if}{if access('clients','new') || access('clients','moveUsages')}
	<tr>
		<td align="right" colspan="2"><input type="submit" name="cl_cards_operations" value="Подтвердить операции" /></td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	{/if}
{/if}

    <TR><TD class=left>Регион:</TD><TD>
        <select name="region" class=text {if $card_type=='addition'}readonly='readonly'{/if}>
        {foreach from=$regions item='r'}
            <option value="{$r.id}"{if $r.id eq $client.region} selected{/if}>{$r.name}</option>
        {/foreach}
        </select>
    </TD></TR>
    <TR><TD class=left width=30%>Компания:</TD><TD><input style='width:100%' name=company class=text value='{$client.company}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
    <TR><TD class=left>Полное название компании:</TD><TD><input style='width:100%' name=company_full class=text value='{$client.company_full}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD class=left>Юридический адрес:</TD><TD><input style='width:100%' name=address_jur class=text value='{$client.address_jur}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD class=left>Почтовый адрес:</TD><TD><input style='width:100%' name=address_post class=text value='{$client.address_post}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD class=left>Действительный почтовый адрес:</TD><TD>
		<input style='width:75%' name=address_post_real class=text value='{$client.address_post_real}'{if $card_type=='addition'}readonly='readonly'{/if}>
		<input type="checkbox" name=mail_print value='yes'{if $client.mail_print == "yes"} checked{/if}{if $card_type=='addition'} readonly='readonly'{/if}>-Печать конвертов<span title="отключает печать через вписку; помечает в счете, что конверты(сопроводительное письмо) не печатаются">*</span>
		</TD></TR>
	<TR><TD class=left>"Кому" письмо<span title="Если поле оставить пустое - то будет вставляться название компании">*</span>:</TD><TD><input style='width:100%' name=mail_who class=text value='{$client.mail_who|escape}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD class=left>Предполагаемый адрес подключения:</TD><TD><input style='width:100%' name=address_connect class=text value='{$client.address_connect}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD class=left>Предполагаемый телефон подключения:</TD><TD><input style='width:100%' name=phone_connect class=text value='{$client.phone_connect}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>

	<TR><TD style='visibility:hidden;font-size:4px' colspan=2>&nbsp;</TD></TR>
	<TR><TD class=left>Головная компания:</TD><TD><input style='width:100%' name=head_company class=text value='{$client.head_company}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD class=left>Юр. адрес головной компании:</TD><TD><input style='width:100%' name=head_company_address_jur class=text value='{$client.head_company_address_jur}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD style='visibility:hidden;font-size:4px' colspan=2>&nbsp;</TD></TR>

	<TR><TD class=left>Станция метро:</TD><TD>{html_options name='metro_id' options=$l_metro selected=$client.metro_id}</TD></TR>
	<TR><TD class=left>Комментарии к платежу:</TD><TD><input style='width:100%' name=payment_comment class=text value='{$client.payment_comment}'></TD></TR>
	<TR><TD style='visibility:hidden;font-size:4px' colspan=2>&nbsp;</TD></TR>
	<TR><TD class=left>Канал продаж:</TD><TD>{html_options name=sale_channel options=$sale_channels selected=$selected_channel}</TD></TR>
	<TR><TD class=left>Телемаркетинг:</TD><TD><SELECT name=telemarketing><option value=''>не определено</option>{foreach from=$users_telemarketing item=item key=user}<option value='{$item.user}'{$item.selected}>{$item.name} ({$item.user})</option>{/foreach}</select></TD></TR>
	<TR><TD class=left>Менеджер:</TD><TD><SELECT name=manager><option value=''>не определено</option>{foreach from=$users_manager item=item key=user}<option value='{$item.user}'{$item.selected}>{$item.name} ({$item.user})</option>{/foreach}</select></TD></TR>
	<TR><TD class=left>Техподдержка:</TD><TD><SELECT name=support><option value=''>не определено</option>{foreach from=$users_support item=item key=user}<option value='{$item.user}'{$item.selected}>{$item.name} ({$item.user})</option>{/foreach}</select></TD></TR>
	<TR><TD style='visibility:hidden;font-size:4px' colspan=2>&nbsp;</TD></TR>
	<TR><TD class=left>Банковские реквизиты:</TD><TD><input style='width:100%' name=bank_properties class=text value='{$client.bank_properties}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD class=left>{if isset($mode_new)}<font color="blue"><b>(1) {/if}ИНН:{if isset($mode_new)}</b></font>{/if}</TD><TD><input id="cl_inn" style='width:100%' {if isset($mode_new)}onKeyUp="statlib.modules.clients.create.findByInn(event)"{/if} name=inn class=text value='{$client.inn}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD class=left>КПП:</TD><TD><input style='width:100%' name=kpp id="cl_kpp" class=text value='{$client.kpp}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
  <TR><TD class=left>Р/С:</TD><TD><input style='width:100%' name=pay_acc class=text value='{$client.pay_acc}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
  <TR><TD class=left><font color="blue"><b>(2) БИК:</b></font></TD><TD><input style='width:100%' onKeyUp="statlib.modules.clients.create.findByBik(event)" name=bik class=text value='{$client.bik}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
  <TR><TD class=left>К/С:</TD><TD><input style='width:100%; background-color: #eeeeee' readonly name=corr_acc value='{$client.corr_acc}'/></TD></TR>
	<TR><TD class=left>Название банка:</TD><TD><input style='width:100%; background-color: #eeeeee' readonly name=bank_name value='{$client.bank_name}'/></TD></TR>
	<TR><TD class=left>Город банка:</TD><TD><input style='width:100%; background-color: #eeeeee' readonly name=bank_city value='{$client.bank_city}'/></TD></TR>
	<TR><TD class=left>ОКПО:</TD><TD><input style='width:100%' name=okpo class=text value='{$client.okpo}'{if $card_type=='addition'}readonly='readonly'{/if}></TD></TR>
	<TR><TD style='visibility:hidden;font-size:4px' colspan=2>&nbsp;</TD></TR>
	<TR><TD class=left>Должность лица, подписывающего договор:</TD><TD><input style='width:100%' name=signer_position class=text value='{$client.signer_position}'></TD></TR>
	<TR><TD class=left>ФИО лица, подписывающего договор:</TD><TD><input style='width:100%' name=signer_name class=text value='{$client.signer_name}'></TD></TR>
	<TR><TD class=left>Должность лица, подписывающего договор, в вин. падеже:</TD><TD><input style='width:100%' name=signer_positionV class=text value='{$client.signer_positionV}'></TD></TR>
	<TR><TD class=left>ФИО лица, подписывающего договор, в вин. падеже:</TD><TD><input style='width:100%' name=signer_nameV class=text value='{$client.signer_nameV}'></TD></TR>
	<TR>
		<TD class=left>Фирма, на которую оформлен договор:</TD>
		<TD>
			<select style='width:100%' name=firma class=text>
				<option value='mcn'{if $client.firma=='mcn'} selected{/if}>ООО &laquo;Эм Си Эн&raquo;</option>
				<option value='markomnet_new'{if $client.firma=='markomnet_new'} selected{/if}>ООО &laquo;МАРКОМНЕТ&raquo;</option>
				<option value='markomnet_service'{if $client.firma=='markomnet_service'} selected{/if}>ООО &laquo;МАРКОМНЕТ сервис&raquo;</option>
				<option value='markomnet'{if $client.firma=='markomnet'} selected{/if}>ООО &laquo;МАРКОМНЕТ (старый)&raquo;</option>
				<option value="ooomcn"{if $client.firma=='ooomcn'} selected{/if}>ООО &laquo;МСН&raquo;</option>
				<option value="all4net"{if $client.firma=='all4net'} selected{/if}>ООО &laquo;ОЛФОНЕТ&raquo;</option>
				<option value="ooocmc"{if $client.firma=='ooocmc'} selected{/if}>ООО &laquo;Си Эм Си&raquo;</option>
				<option value="mcn_telekom"{if $client.firma=='mcn_telekom'} selected{/if}>ООО &laquo;МСН Телеком&raquo;</option>
				<option value="mcm"{if $client.firma=='mcm'} selected{/if}>ООО &laquo;МСМ&raquo;</option>
        <option value="all4geo"{if $client.firma=='all4geo'} selected{/if}>ООО &laquo;Олфогео&raquo;</option>
			</select>
			</TD></TR>
	<TR><TD class=left>Печатать штамп:</TD><TD><select name=stamp class=text><option value=0{if $client.stamp==0} selected{/if}>нет</option><option value=1{if $client.stamp==1} selected{/if}>да</option></select></td></tr>
	<TR><TD class=left>НДС 0%:</TD><TD><input type=checkbox name=nds_zero value=1{if $client.nds_zero} checked{/if}></td></tr>
	<TR><TD style='visibility:hidden' colspan=2>&nbsp;</TD></TR>
	<TR><TD class=left>Нал:</TD><TD><select name=nal class=text><option value='beznal'{if $client.nal=='beznal'} selected{/if}>безнал</option><option value='nal'{if $client.nal=='nal'} selected{/if}>нал</option><option value='prov'{if $client.nal=='prov'} selected{/if}>пров</option></select></td></tr>
	<TR><TD class=left>Валюта:</TD><TD>
		<select name=currency class=text><option value='RUR'{if $client.currency=='RUR'} selected{/if}>RUR</option><option value='USD'{if $client.currency=='USD'} selected{/if}>USD</option></select>
		{if $client.currency=='USD'}
			и счета выставлять в <select name=currency_bill class=text><option value='USD'{if $client.currency_bill!='RUR'} selected{/if}>USD</option><option value='RUR'{if $client.currency_bill=='RUR'} selected{/if}>RUR</option></select>
		{/if}
	</TD></TR>
	<TR><TD style='visibility:hidden' colspan=2>&nbsp;</TD></TR>
	<TR><TD class=left{if $client.voip_disabled or $voip_counters.auto_disabled} style="background-color: #f4a0a0;"{/if}><b>Телефония:</b></TD><TD{if $client.voip_disabled or $voip_counters.auto_disabled} style="background-color: #f4a0a0;"{/if}>
      <label><input type="checkbox" name="voip_disabled" value=1{if $client.voip_disabled} checked{/if}> - Выключить телефонию (МГ, МН, Местные мобильные)</label>
    	</td></tr>
	<TR><TD class=left{if $voip_counters.need_lock_credit} style="background-color: #f4a0a0;"{/if}>Кредит:</TD>
    <TD{if $voip_counters.need_lock_credit} style="background-color: #f4a0a0;"{/if}>
      <label><input id="credit_flag" type="checkbox" {if $client.credit >= 0 }checked{/if} onclick="set_credit_flag()">Использовать</label> &nbsp;&nbsp;&nbsp;&nbsp;<span id="credit_size_block" style="{if $client.credit < 0 }display:none;{/if}">Размер кредита: <input id="credit_size" name=credit class=text value='{$client.credit}'></span> Текущий баланс: {$voip_counters.balance}</td></tr>
	<TR><TD class=left{if $voip_counters.need_lock_limit_month} style="background-color: #f4a0a0;"{/if}>Телефония, лимит использования (месяц):</TD><TD{if $voip_counters.need_lock_limit_month} style="background-color: #f4a0a0;"{/if}><input name=voip_credit_limit class=text value='{$client.voip_credit_limit}'> Расход за месяц: {$voip_counters.amount_month_sum}</td></tr>
	<TR><TD class=left{if $voip_counters.need_lock_limit_day} style="background-color: #f4a0a0;"{/if}>Телефония, лимит использования (день):</TD><TD{if $voip_counters.need_lock_limit_day} style="background-color: #f4a0a0;"{/if}><input name=voip_credit_limit_day class=text value='{$client.voip_credit_limit_day}'> Расход за день: {$voip_counters.amount_day_sum}
      <label><input type="checkbox" name="voip_is_day_calc" value=1{if $client.voip_is_day_calc} checked{/if}> - Включить пересчет дневного лимита</label>
    </td></tr>
	<TR><TD style='visibility:hidden' colspan=2>&nbsp;</TD></TR>
	<TR><TD class=left>Пароль:</TD><TD><input style='width:100%' name=password class=text value='{$client.password}'></td></tr>
	<TR><TD style='visibility:hidden;font-size:4px' colspan=2>&nbsp;</TD></TR>
	<TR><TD class=left> "Абонентская плата по" на "Оказанные услуги по Договору"</TD><TD><input type=checkbox name="bill_rename1" value="yes"{if $client.bill_rename1 == "yes"} checked{/if}></td></tr>
	<TR><TD class=left>USD уровень в процентах:</TD><TD><input style='width:100%' name=usd_rate_percent class=text value='{$client.usd_rate_percent}'></td></tr>
	<TR><TD class=left>Тип:</TD>
		<TD><select name=type class=text>
				<option id="cl_type_org" value='org'{if $client.type=='org'} selected{/if}>Юр.лицо</option>
				<option id="cl_type_priv" value='priv'{if $client.type=='priv'} selected{/if}>Физ.лицо</option>
				<option id="cl_type_org" value='office'{if $client.type=='office'} selected{/if}>Офис</option>
				<option id="cl_type_org" value='multi'{if $client.type=='multi'} selected{/if}>Магазин</option>
				<option id="cl_type_org" value='distr'{if $client.type=='distr'} selected{/if}>Дистрибьютор</option>
			</select></td></tr>
	<TR><TD class=left>ID в All4Net:</TD><TD><input style='width:100%' name=id_all4net class=text value='{$client.id_all4net}'></td></tr>
	<TR><TD class=left>Наследовать права пользователя:</TD><TD><input style='width:100%' name=user_impersonate class=text value='{$client.user_impersonate}'></td></tr>
	<TR><TD class=left>Комментарий для дилера:</TD><TD><input style='width:100%' name=dealer_comment class=text value='{$client.dealer_comment}'></td></tr>
	<TR><TD class=left>Формирование с/ф:</TD><TD>
		<select name=form_type class=text>
			<option value='manual'{if $client.form_type=='manual'} selected{/if}>ручное</option>
			<option value='bill'{if $client.form_type=='bill'} selected{/if}>при выставлении счета</option>
			<option value='payment'{if $client.form_type=='payment'} selected{/if}>при внесении платежа</option>
		</select>
	</td></tr>
	<TR><TD class=left>Тип цены:</TD><TD>
    {html_options options=$l_price_type selected=$client.price_type name="price_type"}
	</td></tr>
</TBODY></TABLE>

{if isset($client.id) && $client.id >0}
<div align=center>Изменения на дату <input type=checkbox name=deferred value=1 id="deferred">
<div id="span_deferred_date" style="display: none; width: 200px; text-align: left;">
<!--input type=text name=deferred_date id=deferred_date-->
<input type=radio name="deferred_date" value="1" checked>с 1го {$history_flags.m.1.n}<br>
<input type=radio name="deferred_date" value="2" checked>с 1го {$history_flags.m.2.n}<br>
<input type=radio name="deferred_date" value="3" checked>с 1го {$history_flags.m.3.n}<br>
<input type=radio name="deferred_date" value="4">с 1го {$history_flags.m.4.n}
</div></div>
{/if}

<DIV align=center><input id=bSubmit onclick="doMainFormSubmit()" class=button type=button value="Изменить"></DIV>

</FORM>

<div id="history_dialog" title="История изменений" style="display: none;"></div>

<div id="dialog_deferred_future" title="Подтверждение изменений" style="display: none;">
<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
С начала следующего месяца уже назначено изменение реквизитов.<hr><br>
<b>Перезаписать</b>?
<br>
<br>
<div align=right><small>Посмотреть историю изменения клиента <img alt="Посмотреть" src="images/icons/edit.gif" class="icon history_open"></small></div>
</div>

<div id="dialog_deferred_past" title="Подтверждение изменений" style="display: none;">
<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
С начала <b id="past"></b> были сделанны изменение реквизитов, <br>они будут перезаписанны введеными реквизитами.<hr><br>
<b>Перезаписать</b>?
<br>
<br>
<div align=right><small>Посмотреть историю изменения клиента <img alt="Посмотреть" src="images/icons/edit.gif" class="icon history_open"></small></div>
</div>


<h3><img alt="Посмотреть" src="images/icons/edit.gif" class="icon history_open">История изменений клиента:</h3>
{if count($log)}{foreach from=$log item=L key=key name=outer}
<b>{$L.ts} - {$L.user}</b>: {$L.comment}<br>
{/foreach}{/if}
<br>
<h3>Дополнительные ИНН</h3>
<table class=insblock cellspacing=4 cellpadding=2 border=0>
<tr><th>ИНН</th><th>комментарий</th><th>кто</th><th>когда</th><th>&nbsp;</th></tr>
{foreach from=$inn item=item}
<tr{if !$item.is_active} class="other"{/if}>
<td>{$item.inn}</td><td>{$item.comment}</td><td>{$item.user}</td><td style='font-size:70%'>{$item.ts}</td><td>
<a href='{$LINK_START}module=clients&id={$item.client_id}&action=inn&act={if $item.is_active}0{else}1{/if}&cid={$item.id}'><img style='margin-left:-2px;margin-top:-3px' class=icon src='{$IMAGES_PATH}icons/{if $item.is_active}delete{else}add{/if}.gif' alt="Активность"></a>
</td></tr>
{/foreach}
<form action="?" method=post><tr>
	<input type=hidden name=module value=clients>
	<input type=hidden name=action value=inn>
	<input type=hidden name=id value='{$client.id}'>
	<td><input class=text style='width:100%' type=text name=inn></td>
	<td colspan=2><input class=text style='width:100%' type=text name=comment></td>
	<td><input class=button type=submit value="добавить"></td>
</tr></form>
</table>

<h3>Дополнительные расчетные счета</h3>
<table class=insblock cellspacing=4 cellpadding=2 border=0>
<tr><th>р/с</th><th>кто</th><th>когда</th><th>&nbsp;</th></tr>
{foreach from=$pay_acc item=item}
<tr>
<td>{$item.pay_acc}</td><td>{$item.user}</td><td style='font-size:70%'>{$item.date}</td><td>
<a href='{$LINK_START}module=clients&id={$item.client_id}&action=pay_acc&cid={$item.id}'><img style='margin-left:-2px;margin-top:-3px' class=icon src='{$IMAGES_PATH}icons/delete.gif' alt="Активность"></a>
</td></tr>
{/foreach}
<form action="?" method=post><tr>
	<input type=hidden name=module value=clients>
	<input type=hidden name=action value=pay_acc>
	<input type=hidden name=id value='{$client.id}'>
	<td><input class=text style='width:100%' type=text name=pay_acc style="width: 100px;"></td>
	<td colspan=2><input class=button type=submit value="добавить"></td>
</tr></form>
</table>
