<link href="/css/behaviors/media-manager.css" rel="stylesheet" />

{if $fixclient}
    <div id="trouble_to_add"{if $tt_show_add} style="display: none;"{/if}>
        <div onClick="$('#trouble_to_add').toggle();$('#trouble_add').toggle();" style="cursor: pointer;">
            <img border="0" src="./images/icons/add.gif"><u>Добавить заявку</u>
        </div>
    </div>
    <div id="trouble_add"{if !$tt_show_add} style="display: none;"{/if}>
        <div onClick="$('#trouble_add').toggle();$('#trouble_to_add').toggle();" style="cursor: pointer;">
            <img border="0" src="./images/icons/add.gif"><u>Добавить заявку (спрятать)</u>
        </div>
        <form action="?" method="POST" id="form" name="form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add" />
            <input type="hidden" name="module" value="tt" />
            {if $curtype}
                <input type="hidden" name="type" value="{$curtype.code}" />
            {/if}
            <table class="mform" cellspacing="0" cellpadding="0" border="0">
                <colgroup>
                    <col width="50%" />
                    <col width="50%" />
                </colgroup>
                <tr>
                    <td valign="top">
                        <table border="0" cellpadding="2" cellspacing="4">
                            {if !$curtype}
                                <tr>
                                    <td class="left" width="30%">Тип заявки</td>
                                    <td>
                                        <select name="type" class="text" style="width: 300px;" onClick='if (this.selectedIndex==null) return; eval("tt_"+this.options[this.selectedIndex].getAttribute("value")+"()");'>
                                            {foreach from=$ttypes item='t'}
                                                <option value="{$t.code}">{$t.name}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                </tr>
                            {/if}

                            <tr>
                                <td class="left">Клиент</td>
                                <td><input name="client" readonly="readonly" value="{if $fixclient}{$fixclient_data.client}{/if}" class="text" style="width: 300px;" /></td>
                            </tr>

                            {if $tt_service}
                                <tr>
                                    <td class="left">Услуга</td>
                                    <td><a href="pop_services.php?table={$tt_service}&id={$tt_service_id}">{$tt_service} #{$tt_service_id}</a></td>
                                    <input type="hidden" name="service" value="{$tt_service}" />
                                    <input type="hidden" name="service_id" value="{$tt_service_id}" />
                                </tr>
                            {/if}

                            {if $tt_server_id}
                                <tr>
                                    <td class="left">Сервер</td>
                                    <td>
                                        <a href="./?module=routers&action=server_pbx_list&id={$tt_server_id}">
                                            &nbsp;{$tt_server.name}, Тех.площадка: {$tt_server.datacenter_name}, Регион: {$tt_server.datacenter_region}
                                        </a>
                                    </td>
                                    <input type="hidden" name="server_id" value="{$tt_server_id}" />
                                </tr>
                            {/if}

                            <tr id="dt_C1" style="display: none;">
                                <td class=left id=dt_C1_capt>Показывать с</td>
                                <td><input type="text" id="date_start" name="date_start" value="{0|udate}" class="text" style="width: 300px;" /></td>
                            </tr>
                            <tr id="dt_A1">
                                <td class="left">Время на устранение</td>
                                <td>
                                    <input type="radio" id="radiostart1" name="A" checked="checked" onClick="start1.disabled=false; start2.disabled=true" />
                                    <input id="start1" type="text" name="time" value="1" class="text" style="text-align:right; width: 180px;" /> час
                                </td>
                            </tr>
                            <tr id="dt_A2">
                                <td class="left">Дата желаемого окончания</td>
                                <td>
                                    <input type="radio" id="radiostart2" name="A" onClick="start1.disabled=true; start2.disabled=false" />
                                    <input id="start2" disabled="disabled" type="text" name=date_finish_desired value="{0|udate}" class="text" style="width: 180px;" />
                                </td>
                            </tr>
                            <tr>
                                <td class="left">Тип заявки:</td>
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
                            </tr>
                            <tr>
                                <td colspan="2">
                                    Текст проблемы:<br /><textarea name="problem" class="textarea"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="left">Ответственный</td>
                                <td>
                                    <select name="user">
                                        {foreach from=$tt_users item=item}
                                            {if $item.user}
                                                <option value='{$item.user}'{if $authuser.user==$item.user} selected{/if}>{$item.name} ({$item.user})</option>
                                            {else}
                                                </optgroup>
                                                <optgroup label="{$item.name}">
                                            {/if}
                                        {/foreach}
                                        </optgroup>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="left">Важная заявка</td>
                                <td><input type="checkbox" name="is_important" value="1" /></td>
                            </tr>
                        </table>
                    </td>
                    <td valign="top">
                        <div class="row" style="padding-left: 50px;">
                            <b>Прикрепить документы к заявке</b><br /><br />
                            <div class="file_upload form-control input-sm">
                                Выбрать файл<input class="media-manager" type="file" name="tt_files[]" />
                            </div>
                            <div class="media-manager-block"></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input id="submit" class="button" type="submit" value="Завести заявку" />
                    </td>
                </tr>
            </table>
        </form>
    </div>

    <script language="javascript">
    {literal}
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

<script type="text/javascript" src="/js/jquery.multifile.min.js"></script>
<script type="text/javascript" src="/js/behaviors/media-manager.js"></script>