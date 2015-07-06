<link rel="stylesheet"  href="css/themes/smoothness/jquery.ui.all.css" type="text/css"/>
<script src="js/jquery-ui-1.9.2.custom.min.js"></script>

<!--script src="js/ui/jquery.ui.datepicker.js"></script-->
<script>
{literal}

function set_credit_flag(){
    if ($('#credit_flag').is(':checked')) {
        if ($('#credit_size')[0].value < 0)
            $('#credit_size')[0].value = '0';
        $('#credit_size_block').show();

    } else {
        $('#credit_size')[0].value = '-1';
        $('#credit_size_block').hide();
    }
}

$(function(){
    $("#deferred").click(function()
    {
        if($(this).is(':checked'))
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
                    $(this).load("./?module=clients&client_id={/literal}{$client.id}{literal}&action=view_history&view_only=true");
                }
            });
    }
    );

    set_credit_flag();
});

{/literal}
    var vPeriod0 = {$history_flags.m.0.v};
    var vPeriod1 = {$history_flags.m.1.v};
    var vPeriod2 = {$history_flags.m.2.v};
    var vPeriod3 = {$history_flags.m.3.v};
    var vPeriod4 = {$history_flags.m.4.v};

    var nPeriod0 = '{$history_flags.m.0.n}';
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

                    if(periodSelected == 0)
                    {
                        fPast = vPeriod0;
                        nPast = nPeriod0;
                    }else if(periodSelected == 1){
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


{if isset($mode_new)}
    <H2>Новый клиент</H2>
{else}
    <div>
    <b>
        <div style="display: inline-block; min-width: 130px;"> Редактирование клиента <a href="?module=clients&id={$client.client}" style="font-size:14px; color: {if $client.is_active}green{else}black{/if};">{$client.id}</a> :</div>
        <div style="display: inline-block;">{$client.company}</div>
    </b>
        <div style="display: inline-block;" title="{$account.client}">({$client.client})</div>
    <div>
{/if}
<form action="?" method=post id=form name=form>
<input style='width:100%' type=hidden name=module value=clients>
{if isset($mode_new)}
<input style='width:100%' type=hidden name=action value=create>
{else}
<input style='width:100%' type=hidden name=action value={if isset($form_action)}{$form_action}{else}apply{/if}>
<input style='width:100%' type=hidden name=id value='{$client.id}'>
{/if}
<table class="table table-condensed table-striped table-hover">
    <tr>
        <td>&nbsp;</td>
        <td>
            <table width="100%">
                <tr>
                    {if isset($mode_new) || (!$client.client && ($client.status=='testing' || $client.status=='negotiations'))}
                        <td>
                            Код клиента:
                            <input style='width:50%' name=client class=text value='{$client.client}'>
                            <a href='#' onclick='form.client.value="id{if $client.id}{$client.id}{else}NNNN{/if}";'>присвоить код {if $client.id}id{$client.id}{/if}</a>
                            {if !$client.id} (idNNNN, определится позднее){/if}
                        </td>
                    {else}
                        <input type=hidden name=client value='{$client.client}'>
                        {if access('clients','new')}
                            <td>
                                Привязать к истории:
                                <select name="previous_reincarnation">
                                    <option value="0"></option>
                                    {if isset($all_cls) && $all_cls}
                                        {foreach from=$all_cls item='c'}<option value="{$c.id}"{if $c.id==$client.previous_reincarnation} selected='selected'{/if}>{$c.client}</option>{/foreach}
                                    {/if}
                                </select>
                            </td>
                            <td><a href="?module=clients&action=mkcontract">Создать новый "договор"</a></td>
                        {/if}
                        {if access('clients','moveUsages')}
                            <td>
                                Забрать услуги:
                                <select name="move_usages">
                                    <option value=""></option>
                                    {if isset($all_cls) && $all_cls}
                                        {foreach from=$all_cls item='c'}{if $client.client<>$c.client}<option value="{$c.id}">{$c.client}{/if}</option>{/foreach}
                                    {/if}
                                </select>
                            </td>
                        {/if}
                        {if access('clients','new') || access('clients','moveUsages')}
                            <td><input type="submit" name="cl_cards_operations" value="Подтвердить операции" /></td>
                        {/if}
                    {/if}
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td align="right">Страна:</td>
        <td>
            <select name="country_id" class="select2" style="width: 200px" {if $card_type=='addition'}readonly='readonly'{/if}>
                {foreach from=$countries item=item}
                    <option value="{$item.code}"{if $item.code eq $contragent.country_id} selected="selected"{/if}>{$item.name}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr><td align="right">Регион:</td><td>
        <select name="region" class="select2" style="width: 200px" {if $card_type=='addition'}readonly='readonly'{/if}>
        {foreach from=$regions item='r'}
            <option value="{$r.id}"{if $r.id eq $client.region} selected{/if}>{$r.name}</option>
        {/foreach}
        </select>
    </td></tr>
    <tr><td align="right" width=30%>{if isset($mode_new)}<font color="blue"><b> {/if}Компания:{if isset($mode_new)}</b></font>{/if}</td><td><input style='width:100%' id="cl_company" name=company class=text value='{$client.company}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td align="right">Полное название компании:</td><td><input style='width:100%' name=company_full class=text value='{$client.company_full}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td align="right">Юридический адрес:</td><td><input style='width:100%' name=address_jur class=text value='{$client.address_jur}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td align="right">Почтовый адрес:</td><td><input style='width:100%' name=address_post class=text value='{$client.address_post}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td align="right">Действительный почтовый адрес:</td><td>
        <input style='width:75%' name=address_post_real class=text value='{$client.address_post_real}'{if $card_type=='addition'}readonly='readonly'{/if}>
        <input type="checkbox" name=mail_print value='yes'{if $client.mail_print == "yes"} checked{/if}{if $card_type=='addition'} readonly='readonly'{/if}>-Печать конвертов<span title="отключает печать через вписку; помечает в счете, что конверты(сопроводительное письмо) не печатаются">*</span>
        </td></tr>
    <tr><td align="right">"Кому" письмо<span title="Если поле оставить пустое - то будет вставляться название компании">*</span>:</td><td><input style='width:100%' name=mail_who class=text value='{$client.mail_who|escape}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td align="right">Предполагаемый адрес подключения:</td><td><input style='width:100%' name=address_connect class=text value='{$client.address_connect}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td align="right">Предполагаемый телефон подключения:</td><td><input style='width:100%' name=phone_connect class=text value='{$client.phone_connect}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr>
        <td align="right">Грузополучатель:</td><td>
            <input style='width:65%' name=consignee class=text value='{$client.consignee}'{if $card_type=='addition'}readonly='readonly'{/if}>
            <input type="hidden" value="0" name="is_with_consignee">
            <input type="checkbox" name=is_with_consignee value=1 {if $client.is_with_consignee} checked{/if}{if $card_type=='addition'} readonly='readonly'{/if}>-Использовать грузополучателя
        </td>
    </tr>
    <tr><td style='font-size:4px' colspan=2>&nbsp;</td></tr>
    <tr><td align="right">Головная компания:</td><td><input style='width:100%' name=head_company class=text value='{$client.head_company}'{*if $card_type=='addition'}readonly='readonly'{/if*}></td></tr>
    <tr><td align="right">Юр. адрес головной компании:</td><td><input style='width:100%' name=head_company_address_jur class=text value='{$client.head_company_address_jur}'{*if $card_type=='addition'}readonly='readonly'{/if*}></td></tr>
    <tr><td align="right">Часовой пояс:</td><td>
            <select class="select2" style="width: 250px" name=timezone_name>
                {foreach from=$timezones item=item key=key}
                    <option value='{$key}' {if $key==$client.timezone_name}selected{/if}>{$item.timezone_name}</option>
                {/foreach}
            </select>
        </td></tr>
    <tr><td style='font-size:4px' colspan=2>&nbsp;</td></tr>

    <tr><td align="right">Станция метро:</td><td>{html_options name='metro_id' options=$l_metro selected=$client.metro_id}</td></tr>
    <tr><td align="right">Комментарии к платежу:</td><td><input style='width:100%' name=payment_comment class=text value='{$client.payment_comment}'></td></tr>
    <tr><td style='font-size:4px' colspan=2>&nbsp;</td></tr>
    <tr><td align="right">Канал продаж:</td><td>{html_options name=sale_channel options=$sale_channels selected=$selected_channel}</td></tr>
    <tr><td align="right">Аккаунт менеджер:</td><td><SELECT class="select2" style="width: 250px" name=account_manager><option value=''>не определено</option>{foreach from=$account_managers item=item key=user}<option value='{$item.user}' {if isset($item.selected)}{$item.selected}{/if}>{$item.name} ({$item.user})</option>{/foreach}</select></td></tr>
    <tr><td align="right">Менеджер:</td><td><SELECT class="select2" style="width: 250px" name=manager><option value=''>не определено</option>{foreach from=$users_manager item=item key=user}<option value='{$item.user}' {if isset($item.selected)}{$item.selected}{/if}>{$item.name} ({$item.user})</option>{/foreach}</select></td></tr>

{if isset($mode_new)}
    <tr><td align="right">Тип договора:</td><td><SELECT class="select2" style="width: 250px" name=contract_type_id id=contract_type_id>{foreach from=$contract_types item=item}<option value='{$item.id}' {if ($item.id == $client.contract_type_id)} selected{/if}>{$item.name}</option>{/foreach}</select></td>
    <tr><td align="right">Бизнес процесс:</td><td><SELECT class="select2" style="width: 250px" name=business_process_id id=business_process_id>{foreach from=$bussines_processes item=item}<option value='{$item.id}' {if ($item.id == $client.business_process_id)} selected{/if}>{$item.name}</option>{/foreach}</select></td>
    <tr><td align="right">Статус бизнес процесса:</td><td><SELECT class="select2" style="width: 250px" name="business_process_status_id" id="business_process_status_id">{foreach from=$bp_statuses item=item}<option value='{$item.id}' {if ($item.id == $client.bussines_processes_status_id)} selected{/if}>{$item.name}</option>{/foreach}</select></td>
    <script>
            optools.client.contractTypeSwitch.init();
    </script>
{/if}
    <tr><td style='font-size:4px' colspan=2>&nbsp;</td></tr>
    <tr><td align="right">Банковские реквизиты:</td><td><input style='width:100%' name=bank_properties class=text value='{$client.bank_properties}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td align="right">{if isset($mode_new)}<font color="blue"><b>(1) {/if}ИНН:{if isset($mode_new)}</b></font>{/if}</td><td><input id="cl_inn" style='width:100%' {if isset($mode_new)}onKeyUp="statlib.modules.clients.create.findByInn(event)"{/if} name=inn class=text value='{$client.inn}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td align="right">{if isset($mode_new)}<font color="blue"><b>(2) {/if}КПП:{if isset($mode_new)}</b></font>{/if}</td><td><input style='width:100%' name=kpp id="cl_kpp" class=text value='{$client.kpp}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
  <tr><td align="right">Р/С:</td><td><input style='width:100%' name=pay_acc class=text value='{$client.pay_acc}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
  <tr><td align="right">БИК:</td><td><input style='width:100%' onKeyUp="statlib.modules.clients.create.findByBik(event)" name=bik class=text value='{$client.bik}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
  <tr><td align="right">К/С:</td><td><input style='width:100%; background-color: #eeeeee' readonly name=corr_acc value='{$client.corr_acc}'/></td></tr>
    <tr><td align="right">Название банка:</td><td><input style='width:100%; background-color: #eeeeee' readonly name=bank_name value='{$client.bank_name}'/></td></tr>
    <tr><td align="right">Город банка:</td><td><input style='width:100%; background-color: #eeeeee' readonly name=bank_city value='{$client.bank_city}'/></td></tr>
    <tr><td align="right">ОКПО:</td><td><input style='width:100%' name=okpo class=text value='{$client.okpo}'{if $card_type=='addition'}readonly='readonly'{/if}></td></tr>
    <tr><td style='font-size:4px' colspan=2>&nbsp;</td></tr>
    <tr><td align="right">Должность лица, подписывающего договор:</td><td><input style='width:100%' name=signer_position class=text value='{$client.signer_position}'></td></tr>
    <tr><td align="right">ФИО лица, подписывающего договор:</td><td><input style='width:100%' name=signer_name class=text value='{$client.signer_name}'></td></tr>
    <tr><td align="right">Должность лица, подписывающего договор, в вин. падеже:</td><td><input style='width:100%' name=signer_positionV class=text value='{$client.signer_positionV}'></td></tr>
    <tr><td align="right">ФИО лица, подписывающего договор, в вин. падеже:</td><td><input style='width:100%' name=signer_nameV class=text value='{$client.signer_nameV}'></td></tr>
    <tr>
        <td align="right">Фирма, на которую оформлен договор:</td>
        <td>
            <select style='width:100%' name="organization_id" class=text>
                {foreach from=$organizations item='organization'}
                    <option value="{$organization.organization_id}"{if $organization.organization_id == $client.organization_id} selected="selected"{/if}>{$organization.name}</option>
                {/foreach}
            </select>
            </td></tr>
    <tr><td align="right">Печатать штамп:</td><td><select name=stamp class=text><option value=0{if $client.stamp==0} selected{/if}>нет</option><option value=1{if $client.stamp==1} selected{/if}>да</option></select></td></tr>
    <tr><td align="right">Без НДС:</td><td><input type=checkbox name=nds_zero value=1{if $client.nds_zero} checked{/if}></td></tr>
    <tr><td style='font-size:4px' colspan=2>&nbsp;</td></tr>
    <tr><td align="right">Нал:</td><td><select name=nal class=text><option value='beznal'{if $client.nal=='beznal'} selected{/if}>безнал</option><option value='nal'{if $client.nal=='nal'} selected{/if}>нал</option><option value='prov'{if $client.nal=='prov'} selected{/if}>пров</option></select></td></tr>
    <tr><td align="right">Валюта:</td><td>
        <select name=currency class=text>
            <option value='RUB'{if $client.currency=='RUB'} selected{/if}>RUB</option>
            <option value='USD'{if $client.currency=='USD'} selected{/if}>USD</option>
            <option value='HUF'{if $client.currency=='HUF'} selected{/if}>HUF</option>
            <option value='EUR'{if $client.currency=='EUR'} selected{/if}>EUR</option>
        </select>
    </td></tr>
    <tr><td style='font-size:4px'colspan=2>&nbsp;</td></tr>
    <tr><td align="right"{if $client.voip_disabled or $voip_counters.auto_disabled} style="background-color: #f4a0a0;"{/if}><b>Телефония:</b></td><td{if $client.voip_disabled or $voip_counters.auto_disabled} style="background-color: #f4a0a0;"{/if}>
      <label><input type="checkbox" name="voip_disabled" value=1{if $client.voip_disabled} checked{/if}> - Выключить телефонию (МГ, МН, Местные мобильные)</label>
        </td></tr>
    <tr><td align="right"{if $voip_counters.need_lock_credit} style="background-color: #f4a0a0;"{/if}>Кредит:</td>
    <td{if $voip_counters.need_lock_credit} style="background-color: #f4a0a0;"{/if}>
      <label><input id="credit_flag" type="checkbox" {if $client.credit >= 0 }checked{/if} onclick="set_credit_flag()">Использовать</label> &nbsp;&nbsp;&nbsp;&nbsp;<span id="credit_size_block" style="{if $client.credit < 0 }display:none;{/if}">Размер кредита: <input id="credit_size" name=credit class=text value='{$client.credit}'></span> Текущий баланс: {$voip_counters.balance}</td></tr>
    <tr><td align="right"{if $voip_counters.need_lock_limit_month} style="background-color: #f4a0a0;"{/if}>Телефония, лимит использования (месяц):</td><td{if $voip_counters.need_lock_limit_month} style="background-color: #f4a0a0;"{/if}><input name=voip_credit_limit class=text value='{$client.voip_credit_limit}'> Расход за месяц: {$voip_counters.amount_month_sum}</td></tr>
    <tr><td align="right"{if $voip_counters.need_lock_limit_day} style="background-color: #f4a0a0;"{/if}>Телефония, лимит использования (день):</td><td{if $voip_counters.need_lock_limit_day} style="background-color: #f4a0a0;"{/if}><input name=voip_credit_limit_day class=text value='{$client.voip_credit_limit_day}'> Расход за день: {$voip_counters.amount_day_sum}
      <label><input type="checkbox" name="voip_is_day_calc" value=1{if $client.voip_is_day_calc} checked{/if}> - Включить пересчет дневного лимита</label>
    </td></tr>
    <tr><td style='font-size:4px' colspan=2>&nbsp;</td></tr>
    <tr><td align="right">Пароль:</td><td><input style='width:100%' name=password class=text value='{$client.password}'></td></tr>
    <tr><td style='font-size:4px' colspan=2>&nbsp;</td></tr>
    <tr><td align="right"> "Абонентская плата по" на "Оказанные услуги по Договору"</td><td><input type=checkbox name="bill_rename1" value="yes"{if $client.bill_rename1 == "yes"} checked{/if}></td></tr>
    <input type="hidden" value="0" name="is_bill_with_refund">
    <input type="hidden" value="0" name="is_bill_only_contract">
    {if $client.status == "operator"}
        <tr>
            <td align="right">Возврат переплаты:</td>
            <td>
                <input type="checkbox" value="1" name="is_bill_with_refund" {if $client.is_bill_with_refund}checked="checked"{/if}>
            </td>
        </tr>
        <tr>
            <td align="right">Услуги по договору ...:</td>
            <td>
                <input type="checkbox" value="1" name="is_bill_only_contract" {if $client.is_bill_only_contract}checked="checked"{/if}>
            </td>
        </tr>
    {/if}
        <tr>
        <td align="right">Печать УПД без подписей</td>
        <td>
            <input type="hidden" value="0" name="is_upd_without_sign">
            <input type="checkbox" value="1" name="is_upd_without_sign" {if $client.is_upd_without_sign == "1"}checked="checked"{/if}>
        </td>
    </tr>
    <tr><td align="right">USD уровень в процентах:</td><td><input style='width:100%' name=usd_rate_percent class=text value='{$client.usd_rate_percent}'></td></tr>
    <tr><td align="right">Тип:</td>
        <td><select name=type class=text>
                <option id="cl_type_org" value='org'{if $client.type=='org'} selected{/if}>Юр.лицо</option>
                <option id="cl_type_priv" value='priv'{if $client.type=='priv'} selected{/if}>Физ.лицо</option>
                <option id="cl_type_ip"   value='ip'{if $client.type=='ip'} selected{/if}>ИП</option>
                <option id="cl_type_org" value='office'{if $client.type=='office'} selected{/if}>Офис</option>
                <option id="cl_type_org" value='multi'{if $client.type=='multi'} selected{/if}>Магазин</option>
                <option id="cl_type_org" value='distr'{if $client.type=='distr'} selected{/if}>Дистрибьютор</option>
                <option id="cl_type_org" value='operator'{if $client.type=='operator'} selected{/if}>Оператор</option>
            </select></td></tr>
    <tr><td align="right">ID в All4Net:</td><td><input style='width:100%' name=id_all4net class=text value='{$client.id_all4net}'></td></tr>
    <tr><td align="right">Наследовать права пользователя:</td><td><input style='width:100%' name=user_impersonate class=text value='{$client.user_impersonate}'></td></tr>
    <tr><td align="right">Комментарий для дилера:</td><td><input style='width:100%' name=dealer_comment class=text value='{$client.dealer_comment}'></td></tr>
    <tr><td align="right">Формирование с/ф:</td><td>
        <select name=form_type class=text>
            <option value='manual'{if $client.form_type=='manual'} selected{/if}>ручное</option>
            <option value='bill'{if $client.form_type=='bill'} selected{/if}>при выставлении счета</option>
            <option value='payment'{if $client.form_type=='payment'} selected{/if}>при внесении платежа</option>
        </select>
    </td></tr>
    <tr>
        <td align="right">Агент:</td>
        <td>
            <input type="hidden" value="N" name="is_agent">
            <input type="checkbox" value="Y" name="is_agent" {if $client.is_agent == "Y"}checked="checked"{/if}>
        </td>
    </tr>
    <tr><td align="right">Тип цены:</td><td>
    {html_options options=$l_price_type selected=$client.price_type name="price_type"}
    </td></tr>
</table>

{if !$client.is_closed}
{if isset($client.id) && $client.id >0}
<div align=center>Изменения на дату <input type=checkbox name=deferred value=1 id="deferred">
<div id="span_deferred_date" style="display: none; width: 200px; text-align: left;">
<!--input type=text name=deferred_date id=deferred_date-->
<input type=radio name="deferred_date" value="0">с 1го {$history_flags.m.0.n}<br>
<input type=radio name="deferred_date" value="1">с 1го {$history_flags.m.1.n}<br>
<input type=radio name="deferred_date" value="2">с 1го {$history_flags.m.2.n}<br>
<input type=radio name="deferred_date" value="3" checked>с 1го {$history_flags.m.3.n}<br>
<input type=radio name="deferred_date" value="4">с 1го {$history_flags.m.4.n}
</div></div>
{/if}

<div align=center><input id=bSubmit onclick="doMainFormSubmit()" class=button type=button value="Изменить"></div>

{else}
<script>
    $("#form input, #form select").attr("readonly", "readonly").attr("disabled", "disabled")
</script>

{/if}
</form>

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
<b>{$L.ts|udate} - {$L.user}</b>: {$L.comment}<br>
{/foreach}{/if}
<br>

<h3>Дополнительные ИНН</h3>
<table class=insblock cellspacing=4 cellpadding=2 border=0>
    <tr><th>ИНН</th><th>комментарий</th><th>кто</th><th>когда</th><th>&nbsp;</th></tr>
    {foreach from=$inn item=item}
    <tr{if !$item.is_active} class="other"{/if}>
    <td>{$item.inn}</td><td>{$item.comment}</td><td>{$item.user}</td><td style='font-size:70%'>{$item.ts|udate}</td><td>
    {if !$client.is_closed}
        <a href='{$LINK_START}module=clients&id={$item.client_id}&action=inn&act={if $item.is_active}0{else}1{/if}&cid={$item.id}'><img style='margin-left:-2px;margin-top:-3px' class=icon src='{$IMAGES_PATH}icons/{if $item.is_active}delete{else}add{/if}.gif' alt="Активность"></a>
    {/if}
    </td></tr>
    {/foreach}
    {if !$client.is_closed}
    <form action="?" method=post><tr>
        <input type=hidden name=module value=clients>
        <input type=hidden name=action value=inn>
        <input type=hidden name=id value='{$client.id}'>
        <td><input class=text style='width:100%' type=text name=inn></td>
        <td colspan=2><input class=text style='width:100%' type=text name=comment></td>
        <td><input class=button type=submit value="добавить"></td>
    </tr></form>
    {/if}
</table>

<h3>Дополнительные расчетные счета</h3>
<table class=insblock cellspacing=4 cellpadding=2 border=0>
    <tr><th>р/с</th><th>кто</th><th>когда</th><th>&nbsp;</th></tr>
    {foreach from=$pay_acc item=item}
    <tr>
    <td>{$item.pay_acc}</td><td>{$item.user}</td><td style='font-size:70%'>{$item.date|udate}</td><td>
        {if !$client.is_closed}
            <a href='{$LINK_START}module=clients&id={$item.client_id}&action=pay_acc&cid={$item.id}'><img style='margin-left:-2px;margin-top:-3px' class=icon src='{$IMAGES_PATH}icons/delete.gif' alt="Активность"></a>
        {/if}
    </td></tr>
    {/foreach}
    {if !$client.is_closed}
    <form action="?" method=post><tr>
        <input type=hidden name=module value=clients>
        <input type=hidden name=action value=pay_acc>
        <input type=hidden name=id value='{$client.id}'>
        <td><input class=text style='width:100%' type=text name=pay_acc style="width: 100px;"></td>
        <td colspan=2><input class=button type=submit value="добавить"></td>
    </tr></form>
    {/if}
</table>
