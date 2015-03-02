<h2>
    {if !$tt_trouble.bill_no}
        <a href='{$LINK_START}module=tt&action=list&mode=1&clients_client={$tt_client.client}'>Заявки</a>
        -
    {/if}
    Заявка
    {if isset($tt_trouble.type)}
        {$tt_trouble.type}
    {/if}
    {$tt_trouble.id}
    {if $tt_trouble.bill_no}
        <span style='font-size:11px'>{mformat param=$tt_trouble.date_creation format='Y.m.d H:i:s'}</span>
    {/if}
</h2>

<table class="table table-condensed table-striped">
{if !$tt_trouble.bill_no}
    {if $tt_client}
        <tr>
            <td align="right">Клиент:</td>
            <td><a href='{$LINK_START}module=clients&id={$tt_client.client}'>{$tt_client.company}</a> ({$tt_client.client})</td>
        </tr>
    {/if}
    {if $tt_trouble.service}
        <tr>
            <td align="right">Услуга:</td>
            <td><a href='pop_services.php?table={$tt_trouble.service}&id={$tt_trouble.service_id}'>{if $tt_trouble.service=="usage_voip"}Телефония {$tt_trouble.number}{else}{$tt_trouble.service} #{$tt_trouble.service_id}{/if}</a></td>
        </tr>
    {/if}
    {if $tt_trouble.server_id}
        <tr>
            <td align="right">Сервер:</td>
            <td><a href='module=routers&action=server_pbx_apply&id={$tt_trouble.server_id}'>{$tt_trouble.server}, Тех.площадка: "{$tt_trouble.datacenter_name}", Регион: {$tt_trouble.datacenter_region}</a></td>
        </tr>
    {/if}
    {if $tt_trouble.bill_no}
        <tr>
            <td align="right">Заказ:</td>
            <td><a href="index.php?module=newaccounts&action=bill_view&bill={$tt_trouble.bill_no}">{$tt_trouble.bill_no}</a></td>
        </tr>
    {/if}
    <tr>
        <td align="right">Трабл создал:</td>
        <td>{$tt_trouble.user_author_name} ({$tt_trouble.user_author}), <span style='font-size:11px'>{mformat param=$tt_trouble.date_creation format='Y.m.d H:i:s'}</span></td>
    </tr>
    <tr>
        <td align="right">Текущие сроки:</td>
        <td>
            с {mformat param=$tt_trouble.date_start format='Y.m.d H:i:s'} по {mformat param=$tt_trouble.date_finish_desired format='Y.m.d H:i:s'}<br>
            {if $tt_trouble.is_active}
                прошло <font color=red>{$tt_trouble.time_pass} / {$tt_trouble.time_limit}</span>
            {else}
                неактивна / {$tt_trouble.time_limit}
            {/if}
        </td>
    </tr>
{/if}
{if $tt_trouble.trouble_subtype}
    <tr>
        <td align="right">Тип заявки: </td>
        <td>{$tt_trouble.trouble_subtype}</td>
    </tr>
{/if}
    <tr>
        <td align="right">Проблема</td>
        <td style="border:1px solid black;background:white;">
            {$tt_trouble.problem|escape|replace:"\\n":"\n"|replace:"\\r":""|replace:"\n\n":"\n"|replace:"\n\n":"\n"|replace:"\n":"<br>"}
        </td>
    </tr>
{if access('tt','time') && $tt_write && $tt_edit}
    <tr>
        <td align="right">Добавить времени (часов)</td>
        <td><form action='?' style='padding:0; margin:0' method=post><input type=hidden name=module value=tt><input type=hidden name=action value=time><input type=hidden name=id value={$tt_trouble.id}><input type=text class=text name=time value='1'> <input type=submit class=button value='Добавить'> (введите отрицательное число, чтобы отнять время)</form></td>
    </tr>
{/if}
{if access('tt','time') && $tt_write && $tt_edit}
    <tr>
        <td  align="right" title="С какого момента показывать">Дата активации </td>
        <td><form action='?' style='padding:0; margin:0' method=post><input type=hidden name=module value=tt><input type=hidden name=action value=time><input type=hidden name=id value={$tt_trouble.id}><input type=text name=date_activation value="{$tt_trouble.date_start}"> <input type=submit class=button value='Установить'></form></td>
    </tr>
{/if}
</table>

<br>

<table style="width: 100%">
    <tr>
        {if $tt_trouble.support_ticket_id}
        <td valign="top">
            <table class="table table-condensed table-striped table-hover">
                <tr>
                    <th>Комментарий</th>
                    <th>Автор</th>
                    <th>Дата</th>
                </tr>
                {foreach from=$ticketComments item=item name=outer}
                    <tr>
                        <td>{$item.text|escape|replace:"\\n":"\n"|replace:"\\r":""|replace:"\n\n":"\n"|replace:"\n\n":"\n"|replace:"\n":"<br>"}</td>
                        <td nowrap>{$item.author}</td>
                        <td nowrap>{$item.created_at}</td>
                    </tr>
                {/foreach}
            </table>

            <h2>Ответ пользователю</h2>
            <form action="/support/comment-ticket" method=post id=form name=form>
                <input type=hidden name="SubmitTicketCommentForm[ticket_id]" value='{$tt_trouble.support_ticket_id}'>
                <textarea name="SubmitTicketCommentForm[text]" class=textarea></textarea>
                <input class=button type=submit value="Ответить">
            </form>
        </td>
        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        {/if}
        <td valign="top">
            <table class="table table-condensed table-striped table-hover">
                <tr>
                    <th width="9%">Состояние</th>
                    <th width="8%">Ответственный</th>
                    <th width="10%">сроки</th>
                    <th width="8%">Этап закрыл</th>
                    <th width="*">с комментарием</th>
                    <th width="15%">время закрытия</th>
                </tr>
                {foreach from=$tt_trouble.stages item=item name=outer}
                    <tr>
                        <td>{$item.state_name}</td>
                        <td>{$item.user_main}</td>
                        <td>{$item.date_start|mdate:'m-d H:i'}<br>{$item.date_finish_desired|mdate:'m-d H:i'}</td>
                        <td>{$item.user_edit}</td>
                        <td>
                            {if count($item.doers)>0}
                            <table border='0' width='100%'>
                                <tr>
                                    <td width='50%'>&nbsp;{/if}{$item.comment|find_urls}{if $item.uspd}<br>{$item.uspd}{/if}{if count($item.doers)>0}</td>
                                    <td width='50%'><table border='0' align='right' style='background-color:lightblue'>
                                            <tr align='center'><td colspan='2'>Исполнители:</td></tr>
                                            {foreach from=$item.doers item='doer'}<tr align='center'><td>{$doer.depart}&nbsp;</td><td>&nbsp;{$doer.name}</td></tr>{/foreach}
                                        </table></td>
                                </tr>
                            </table>
                            {/if}
                            {if isset($item.doer_stages) && $item.doer_stages}
                                <table border=0 colspan=0 rowspan=0>
                                    {foreach from=$item.doer_stages item=ds}<tr><td>{$ds.date}</td><td>{$ds.status_text}({$ds.status})</td><td>{$ds.comment}</td></tr>{/foreach}
                                </table>
                            {/if}
                            {if $item.rating > 0}
                                <br>
                                Оценка: {$item.user_rating}: <b>{$item.rating}</b>
                            {/if}
                        </td>
                        <td>{$item.date_edit}</td>
                    </tr>
                {/foreach}
            </table>

            {if ($tt_write || $tt_doComment) && $tt_edit || (access('tt', 'rating') && !$tt_edit && !$rated && $tt_trouble.state_id == 2)}{*не закрыт или закрыт и рейтинг не стоит*}
                <form action="index_lite.php" method="post" id="state_1c_form">
                    <input type="hidden" name="module" value="tt" />
                    <input type="hidden" name="action" value="rpc_setState1c" />
                    <input type=hidden name="id" value='{$tt_trouble.id}' />
                    <input type="hidden" id="state_1c_form_bill_no" name="bill_no" value="{$tt_trouble.bill_no}" />
                    <input type="hidden" id="state_1c_form_state" name="state" value="" />
                </form>

                <h2>Этап</h2>
                <form action="./?" method=post id=form name=form>
                    <input type=hidden name=action value=move>
                    <input type=hidden name=module value=tt>
                    <input type=hidden name=id value='{$tt_trouble.id}'>
                    <table class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
                        <tr>
                            <td>Комментарий:</td>
                            <td><textarea name=comment class=textarea>{if isset($stage.comment)}{$stage.comment}{/if}</textarea></td>
                        </tr>

                        {if $tt_write}
                            <tr>
                                <td>Новый ответственный:</td>
                                <td>
                                    {if isset($admin_order) && $admin_order && $order_editor != "stat"}
                                        {foreach from=$tt_users item=item}
                                            {if $tt_trouble.user_main==$item.user}
                                                <input type=hidden name=user value={$item.user}>{$item.name} ({$item.user})
                                            {/if}
                                        {/foreach}
                                    {else}
                                        <select class="select2" style="width: 250px" name=user>
                                            {foreach from=$tt_users item=item}
                                            {if $item.user}
                                            <option value='{$item.user}'{if $tt_trouble.user_main==$item.user} selected{/if}>{$item.name} ({$item.user})</option>
                                            {else}
                                            </optgroup>
                                            <optgroup label="{$item.name}">

                                                {/if}
                                                {/foreach}
                                            </optgroup>
                                        </select>
                                    {/if} {*admin_order:end*}
                                </td>
                            </tr>
                            {if $tt_trouble.is_important}
                                <tr>
                                    <td style="color: #c40000;"><b>Важная заявка</b></td>
                                    <td>&nbsp;</td>
                                </tr>
                            {/if}
                            <tr>
                                <td>Новое состояние:</td>
                                <td>
                                    {if isset($admin_order) && $admin_order && $order_editor != "stat"}
                                        {foreach from=$tt_states item=item}
                                            {if $tt_trouble.state_id==$item.id}{$item.name}
                                                <input type=hidden name='state' value='{$item.id}'>
                                            {/if}
                                        {/foreach}
                                    {else}
                                        <select name='state' onChange="
                                                tuspd.style.display=(document.getElementById('state_3') && state_3.selected?'':'none');
                                        {if access('tt','rating')} onChangeSelectState(this); {/if}
                                                ">
                                            {foreach from=$tt_states item=item}
                                                {if !isset($tt_restrict_states) || !($item.pk & $tt_restrict_states)}
                                                    <option id='state_{$item.id}' data-id="{$item.id}" value='{$item.id}'{if $tt_trouble.state_id==$item.id} selected{/if}>{$item.name}</option>
                                                {/if}
                                            {/foreach}
                                        </select>
                                        {if isset($admin_order) && $admin_order}
                                            <input type=submit value="Предать в admin.markomnet" name="to_admin" class=button>
                                        {/if}
                                    {/if}
                                    <span id="rating" style="display: none";>
                                    &nbsp; Оценка:
                                    {if $rated}
                                        <b>{$rated}</b>
                                    {else}
                                        <select name=trouble_rating>
                                            <option value=0>-----</option>
                                            <option value=1>1</option>
                                            <option value=2>2</option>
                                            <option value=3>3</option>
                                            <option value=4>4</option>
                                            <option value=5>5</option>
                                        </select>
                                    {/if}
                                    </span>
                                </td>
                            </tr>
                            {if isset($bill) && $bill}
                                <tr>
                                <td>Статус заказа в 1С: </td>
                                <td>
                                    <b>{$bill.state_1c}</b>
                                    {if $tt_1c_states}
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        {foreach from=$tt_1c_states item='s'}
                                            <input type="button" value="{$s}" onclick="statlib.modules.tt.mktt.setState1c(event,this)" />
                                        {/foreach}
                                    {/if}
                                </td>
                                </tr>{/if}
                            <tr id=tuspd style='display:none'>
                                <td>Номер заявки в УСПД:</td>
                                <td><input type=text class=text name=uspd value=""></td>
                            </tr>
                            {if !(isset($admin_order) && $admin_order) || $order_editor == "stat"}
                                <tr>
                                    <td>Выбрать исполнителя</td>
                                    <td>
                                        <input type="checkbox" name="showTimeTable"{if isset($timetableShow)} checked='checked'{/if}
                                               onclick="if(timetable_pane.style.display=='none')timetable_pane.style.display='block';else timetable_pane.style.display='none'" />
                                    </td>
                                </tr>
                            {/if}
                        {/if}
                        <tr><td colspan="2">&nbsp</td></tr>
                    </table>
                    <div align=center><input id=submit class=button type=submit value="Добавить"></div>
                    {include file='tt/timetable.tpl'}
                </form>

            {/if}

            {if access('tt','rating')}
                <script>
                    {literal}
                    function onChangeSelectState(o)
                    {
                        var stateId = $(o).find(':selected').data('id');
                        if(stateId == 7 || stateId == 2)
                        {
                            $('#rating').show();
                        }else{
                            $('#rating').hide();
                        }
                    }
                    {/literal}
                    {if $tt_trouble.state_id == 2 || $tt_trouble.state_id == 7}$('#rating').show();{/if}
                </script>
            {/if}
        </td>
    </tr>
</table>
