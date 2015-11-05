<h2>
    Бухгалтерия {$fixclient_data.id} &nbsp;&nbsp;&nbsp;
    <span style="font-size: 10px;">
        (
            <a href="?module=newaccounts&simple=1">попроще</a>
            {if $fixclient_data.id_all4net} | <a href="http://all4net.ru/admin/users/balance.html?id={$fixclient_data.id_all4net}">all4net</a>{/if}
            {if $fixclient_data.type == 'multi'}
                |
                <a href="./?module=newaccounts&view_canceled={if $view_canceled}0{else}1{/if}">
                    {if $view_canceled}Скрыть{else}Показать{/if} отказные счета
                </a>
            {/if}
        )
    </span>
</h2>

<a href="{$LINK_START}module=newaccounts&action=bill_create">Создать счёт</a> /
<a href="{$LINK_START}module=newaccounts&action=bill_balance">Обновить баланс</a><br /><br />

<span title="Клиент должен нам">Входящее сальдо</span>:
<form style="display: inline;" action="?" method="POST" onSubmit="return optools.bills.checkSubmitSetSaldo();">
    <input type="hidden" name="module" value="newaccounts" />
    <input type="hidden" name="action" value="saldo" />
    <input type="text" class="text" style="width: 70px; border:0; text-align: center;" name="saldo" value="{if isset($sum_cur.last_saldo)}{$sum_cur.last_saldo}{/if}" />
    <input type="text" class="text" style="width: 12px; border:0" readonly="1" value="{if $fixclient_data.currency=='USD'}${else}р{/if}" />
    на дату <input id="date" type="text" class="text" style="width: 85px; border: 0;" name="date" value="{if $sum_cur.last_saldo_ts}{$sum_cur.last_saldo_ts|udate|mdate:"Y-m-d"}{/if}" />
    <input type="submit" class="button" value="ok" />
</form>

&nbsp; <a href="javascript:toggle2(document.getElementById('saldo_history'))">&raquo;</a><br />
<table style="display: none; margin-left: 20px;" class="price" id="saldo_history">
    <tr>
        <td class="header">Дата изменения</td>
        <td class="header">Пользователь</td>
        <td class="header">Сальдо</td>
        <td class="header">Дата сальдо</td>
        <td class="header"></td>
    </tr>
    {foreach from=$saldo_history item=item}
        <tr class="even">
            <td>{$item.edit_time|udate_with_timezone}</td>
            <td>{$item.user_name}</td>
            <td>{if isset($item.saldo)}{$item.saldo}{/if} {if $item.currency=='USD'}${else}р{/if}</td>
            <td>{$item.ts|udate_with_timezone}</td>
            <td><a href="/client/cancel-saldo?id={$item.id}&clientId={$fixclient_data.id}" onClick="return confirm('Вы уверены, что хотите отменить сальдо ?')"><b>отменить</b></a></td>
        </tr>
    {/foreach}
</table>

<table width="100%">
    <tr>
        <td valign="top" width="50%">
            <table width="100%" border="0">
                <tr style="background-color: #eaeaea;">
                    <td>Всего залогов:</td>
                    <td align="right"> <b>{$sum_l.zalog.RUB|money:'RUB'}</b> </td>
                    <td></td>
                    <td align="right"> <b>{$sum_l.zalog.USD|money:'USD'}</b> </td>
                </tr>
                <tr>
                    <td>Всего платежей:</td>
                    <td align="right"> <b>{$sum_l.payments|default:'0.00'|money:'RUB'}</b></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr style="background-color: #eaeaea;">
                    <td>Общая сумма оказанных услуг:</td>
                    <td align="right"><b>{if $fixclient_data.currency=='USD'}{$sum.RUB.bill|money:'RUB'}{else}{$sum_cur.bill|money:'RUB'}{/if}</b></td>
                    <td></td>
                    <td align="right"><b>{if $fixclient_data.currency=='USD'}{$sum_cur.bill|money:'USD'}{else}{$sum.USD.bill|money:'USD'}{/if}</b></td>
                </tr>
                {if $fixclient_data.status == 'distr' or $fixclient_data.status == 'operator'}
                    <tr>
                        <td>По счетам:</td>
                        <td align="right" colspan="3"> <b>+{$bill_total_add.p} / {$bill_total_add.n} = {$bill_total_add.t}</b>
                    </tr>
                {/if}
                <tr  style="background-color: #eaeaea;">
                    <td>Общая сумма <span title="Клиент должен нам">долга</span> (с учётом сальдо):</td>
                    <td align="right">
                        <b>
                            {if $fixclient_data.currency!='USD'}
                                {if isset($sum_cur.saldo)}{$sum_cur.delta+$sum_cur.saldo|money:'RUB'}{else}{$sum_cur.delta|money:'RUB'}{/if}
                            {else}
                                {if isset($sum.RUB.saldo)}{$sum.RUB.delta+$sum.RUB.saldo|money:'RUB'}{else}{$sum.RUB.delta|money:'RUB'}{/if}
                            {/if}
                        </b>
                    </td>
                    <td></td>
                    <td align="right">
                        &nbsp;
                    </td>
                </tr>
            </table>
        </td>
        <td valign="top" style="padding-left: 100px;" align="right">
            <div>
                <form action="?" name="show_incomegoods" method="get">
                    <input type="hidden" name="module" value="newaccounts" />
                    <input type="hidden" name="action" value="show_income_goods" />
                    <input id="with_income" type="checkbox" value="Y" name="show" {if $get_income_goods_on_bill_list}checked{/if} onchange="show_income_goods();" />
                    <label for="with_income">Показывать заказы поставщика</label>
                </form>
            </div>
            {if $counters}
                <table>
                    <tr>
                        <td>
                            <b>IP-Телефония:</b><br/>
                            Расход за день: <b>{$counters.amount_day_sum}</b><br/>
                            Расход за месяц: <b>{$counters.amount_month_sum}</b><br/>
                            Текущий баланс: <b>{$fixclient_data.balance+$counters.amount_sum} {$fixclient_data.currency}</b><br/>
                        </td>
                    </tr>
                </table>
            {/if}
        </td>
    </tr>
</table>

{include file='newaccounts/bill_list_part_transactions.tpl'}

<table class="price" cellspacing="3" cellpadding="1" border="0" width="100%">
    <tr>
        <td class="header" valign="bottom" colspan="3">Счёт</td>
        <td class="header" valign="bottom">&nbsp;</td>
        <td class="header" valign="bottom" colspan="3">Платёж</td>
        <td class="header" valign="bottom" colspan="3">Разбивка оплаты</td>
        <td class="header" valign="bottom" rowspan="2">Привязка</td>
        <td class="header" valign="bottom" rowspan="2">Документы</td>
    </tr>
    <tr>
        <td class="header" valign="bottom">Дата</td>
        <td class="header" valign="bottom">Номер</td>
        <td class="header" valign="bottom">Сумма</td>
        <td class="header" valign="bottom" title="положительные числа - мы должны клиенту, отрицательные - клиент нам">разница</td>
        <td class="header" valign="bottom">Сумма</td>
        <td class="header" valign="bottom">Дата</td>
        <td class="header" valign="bottom">Кто</td>
        <td class="header" valign="bottom">разница</td>
        <td class="header" valign="bottom">Сумма оплаты</td>
        <td class="header" valign="bottom">Дата платежа</td>
    </tr>
    {foreach from=$billops item=op key=key name=outer}
        {count_comments v=$op}
        {if isset($op.bill) && (($op.bill && $op.bill.currency!=$fixclient_data.currency) || (!$op.bill && (count($op.pays)==1) && !$op.pays.0.in_sum))}
            {assign var=class value=other}
        {else}
            {cycle values="even,odd" assign=class}
        {/if}
        <tr class="{$class}" style="{if $op.isCanceled==1}text-decoration: line-through;{/if}">
            {if isset($op.bill) && $op.bill}
                <td rowspan="{$rowspan}"{if $op.bill.postreg!="0000-00-00"} style="background-color:#FFFFD0"{/if}>{$op.bill.bill_date}</td>
                <td rowspan="{$rowspan}" class="pay{$op.bill.is_payed}">
                    <a href="{$LINK_START}module=newaccounts&action=bill_view&{if $op.bill.type == "income_order"}income_order_id={$op.bill.bill_id}{else}bill={$op.bill.bill_no}{/if}">{$op.bill.bill_no}{if strlen($op.bill.bill_no_ext)}<br />({$op.bill.bill_no_ext}){/if}</a></td>
                <td rowspan="{$rowspan}" align="right">{$op.bill.sum|money:$op.bill.currency}</td>
            {else}
                <td colspan="3" rowspan="{$rowspan}">&nbsp;</td>
            {/if}
            <td rowspan="{$rowspan}" align="right">
                {if $fixclient_data.currency == $op.bill.currency}
                    {objCurrency op=$op obj='delta' currency=$fixclient_data.currency}
                {/if}
            </td>
            {if count($op.pays)}
                {foreach from=$op.pays item=pay key=keyin name=inner}
                    {if $smarty.foreach.inner.iteration!=1}
                        </tr><tr class="{$class}">
                    {/if}
                    {if isset($pay.p_bill_no) && isset($op.bill.bill_no) && $pay.p_bill_no==$op.bill.bill_no}
                        <td>{objCurrency op=$op obj='pay_full' pay=$pay currency=$fixclient_data.currency}</td>
                        <td style='font-size:85%'>{$pay.payment_date} - &#8470;{$pay.payment_no} /
                            {if $pay.type=='bank'}b{elseif $pay.type=='prov'}p{elseif $pay.type=='neprov'}n{elseif $pay.type=='webmoney'}wm{elseif $pay.type=='yandex'}y{else}{$pay.type}{/if}
                            {if $pay.oper_date!="0000-00-00"} - {$pay.oper_date}{/if}
                        </td>
                        <td align="right">
                            <span title="{$pay.add_date}">{$pay.user_name}</span>
                            {if (access('newaccounts_payments','delete') && $pay.type != 'ecash')}
                                <a onClick="return confirm('Вы уверены?')" href="/payment/delete?paymentId={$pay.id}"><img class="icon" src="{$IMAGES_PATH}icons/delete.gif" alt="Удалить"></a>{/if}
                        </td>
                    {else}
                        <td colspan="3">&nbsp;</td>
                    {/if}
                    {if $smarty.foreach.inner.iteration==1}
                        <td rowspan="{$rowspan}" align="right">{objCurrency op=$op obj='delta2' currency=$fixclient_data.currency}</td>
                    {/if}

                    {if isset($op.bill.bill_no) && isset($pay.bill_no) && $pay.bill_no==$op.bill.bill_no}
                        {if $pay.payment_id|strpos:"-"}
                            <td style="{if $pay.p_bill_no!=$pay.bill_no}background: #e0e0ff;{/if}">{objCurrency op=$op obj='pay2' pay=$pay currency=$fixclient_data.currency}</td>
                            <td style="font-size: 85%;{if $pay.p_bill_no!=$pay.bill_no}background: #e0e0ff;{/if}">&#8470;{$pay.payment_id}</td>
                        {else}
                            <td style="{if $pay.p_bill_no!=$pay.bill_no}background: #e0e0ff;{/if}">{objCurrency op=$op obj='pay2' pay=$pay currency=$fixclient_data.currency}</td>
                            <td style="font-size: 85%;{if $pay.p_bill_no!=$pay.bill_no}background: #e0e0ff;{/if}">
                                {$pay.payment_date} - &#8470;{$pay.payment_no} /
                                {if $pay.type=='bank'}b({$pay.bank})
                                {elseif $pay.type=='prov'}p
                                {elseif $pay.type=='neprov'}n
                                {elseif $pay.type=='webmoney'}wm
                                {elseif $pay.type=='yandex'}y
                                {else}{$pay.type}{/if}
                                {if $pay.oper_date!="0000-00-00"} - {$pay.oper_date}{/if}
                            </td>
                        {/if}
                    {else}
                        <td colspan="2">&nbsp;</td>
                    {/if}
                    <td style="padding:0 0 0 0">
                        {if ($op.delta>=0.01) && access('newaccounts_payments','edit')}
                            <form name="paybill{$pay.id}" style="display: inline; width:'';" action="?">
                            <input type="hidden" name="module" value="newaccounts" />
                            <input type="hidden" name="action" value="pay_rebill" />
                            <input type="hidden" name="pay" value="{$pay.id}" />
                            <select name="bill" onChange="paybill{$pay.id}.submit()" class="text" style="border: 0; padding: 0; margin: 0; width:''">
                                <option></option>
                                {foreach from=$billops item=sop key=key name=innerselect}
                                    {if isset($sop.bill) && $sop.bill && ($sop.delta<0)}
                                        <option value="{$sop.bill.bill_no}"{if $pay.p_bill_vis_no==$sop.bill.bill_no} selected="selected"{/if}>{$sop.bill.bill_no}</option>
                                    {/if}
                                {/foreach}
                            </select>
                            </form>
                        {/if}
                    </td>
                    {if $smarty.foreach.inner.iteration==1}
                        <td rowspan="{$rowspan}">
                            {if isset($qrs[$op.bill.bill_no].11) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].11}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].11}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].11}" target=_blank title="Акт-1"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].11}"></a>А1<br/>{/if}
                            {if isset($qrs[$op.bill.bill_no].12) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].12}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].12}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].12}" target=_blank title="Акт-2"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].12}"></a>A2<br/>{/if}
                            {if isset($qrs[$op.bill.bill_no].21) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].21}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].21}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].21}" target=_blank title="УПД-1"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].21}"></a>У1<br/>{/if}
                            {if isset($qrs[$op.bill.bill_no].22) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].22}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].22}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].22}" target=_blank title="УПД-2"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].22}"></a>У2<br/>{/if}
                            {if isset($qrs[$op.bill.bill_no].23) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].23}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].23}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].23}" target=_blank title="УПД-3"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].23}"></a>У3{/if}
                        </td>
                    {/if}
                    {if $pay.comment}
                        </tr>
                        <tr class="{$class}">
                            <td colspan="4" class="comment">{$pay.comment|escape:"html"}</td>
                            <td colspan="2">&nbsp;</td>
                    {/if}
                {/foreach}
                {if $op.bill.comment}
                    </tr>
                    <tr class="{$class}">
                        <td colspan="4" class="comment">{$op.bill.comment|escape:"html"}</td>
                        <td colspan="5">&nbsp;</td>
                        <td colspan="2">&nbsp;</td>
                {/if}
            {else}
                <td colspan="7" rowspan="1">&nbsp;</td>
                <td>
                    {if isset($qrs[$op.bill.bill_no].11) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].11}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].11}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].11}" target=_blank title="Акт-1"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].11}"></a>А1<br/>{/if}
                    {if isset($qrs[$op.bill.bill_no].12) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].12}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].12}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].12}" target=_blank title="Акт-2"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].12}"></a>A2<br/>{/if}
                    {if isset($qrs[$op.bill.bill_no].21) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].21}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].21}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].21}" target=_blank title="УПД-1"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].21}"></a>У1<br/>{/if}
                    {if isset($qrs[$op.bill.bill_no].22) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].22}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].22}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].22}" target=_blank title="УПД-2"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].22}"></a>У2<br/>{/if}
                    {if isset($qrs[$op.bill.bill_no].23) && isset($op.bill.bill_no) && $qrs[$op.bill.bill_no].23}<a {if access('newaccounts_bills','del_docs')}class="del_doc"{/if} id="{$qrs[$op.bill.bill_no].23}" href="./?module=newaccounts&action=doc_file&id={$qrs[$op.bill.bill_no].23}" target=_blank title="УПД-3"><img border=0 src="images/icons/act.gif" title="{$qrs_date[$op.bill.bill_no].23}"></a>У3{/if}
                </td>

                {if isset($op.bill.comment) && $op.bill.comment}
                    </tr>
                    <tr class="{$class}">
                        <td colspan="6" class="comment">{$op.bill.comment|escape:"html"}</td>
                {/if}
            {/if}
        </tr>
        {if isset($op.switch_to_mcn) &&  $op.switch_to_mcn}
            <tr>
                <td colspan="12" style="padding:0 0 0 0;margin: 0 0 0 0;background-color: #9edbf0; font-size: 8pt; text-align: center;">{$op.switch_to_mcn}</td>
            </tr>
        {/if}
    {/foreach}
</table>

{if access('newaccounts_bills','del_docs')}
    <script type="text/javascript">
    {literal}
        $(document).ready(function(){
            statlib.modules.newaccounts.bill_list_full.simple_tooltip(".del_doc" ,"tooltip");
        });
    {/literal}
    </script>
{/if}
<script type="text/javascript">
    {literal}
        function show_income_goods()
        {
            document.forms["show_incomegoods"].submit();
        }
        $( '#date').datepicker({dateFormat: 'yy-mm-dd'});
    {/literal}
</script>
