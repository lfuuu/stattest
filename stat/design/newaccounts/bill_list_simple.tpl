<h2>
    Бухгалтерия {$fixclient_data.id} &nbsp;&nbsp;&nbsp;
    <span style="font-size: 10px;">
        (
            <a href="?module=newaccounts&simple=0">посложнее</a>
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
    на дату <input id="date" type="text" class="text" style="width: 85px; border: 0" name="date" value="{if $sum_cur.last_saldo_ts}{$sum_cur.last_saldo_ts|udate|mdate:"Y-m-d"}{/if}" />
    <input type="submit" class="button" value="ok" />
</form>

&nbsp; <a href="javascript:toggle2(document.getElementById('saldo_history'))">&raquo;</a><br />
<table style="display:none;margin-left:20px" class="price" id="saldo_history">
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

<table width=100%>
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
                <tr  style="background-color: #eaeaea;">
                    <td>Общая сумма оказанных услуг:</td>
                    <td align="right"><b>{if $fixclient_data.currency=='USD'}{$sum.RUB.bill|money:'RUB'}{else}{$sum_cur.bill|money:'RUB'}{/if}</b></td>
                    <td></td>
                    <td align="right"><b>{if $fixclient_data.currency=='USD'}{$sum_cur.bill|money:'USD'}{else}{$sum.USD.bill|money:'USD'}{/if}</b></td>
                </tr>
                <tr>
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
        <td valign=top style="padding-left: 100px;" align="right">
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
        <td class="header" valign="bottom" colspan="4">Платёж</td>
    </tr>
    <tr>
        <td class="header" valign="bottom">Дата</td>
        <td class="header" valign="bottom">Номер</td>
        <td class="header" valign="bottom">Сумма</td>
        <td class="header" valign="bottom" title="положительные числа - мы должны клиенту, отрицательные - клиент нам">разница</td>
        <td class="header" valign="bottom">Сумма</td>
        <td class="header" valign="bottom">Дата</td>
        <td class="header" valign="bottom">Курс</td>
        <td class="header" valign="bottom">Кто</td>
    </tr>
    {foreach from=$billops item=op key=key name=outer}
        {count_comments v=$op}
        {if (isset($op.bill) && $op.bill && $op.bill.currency!=$fixclient_data.currency) || ((!isset($op.bill) || !$op.bill) && (count($op.pays)==1) && !$op.pays.0.in_sum)}
            {assign var=class value=other}
        {else}
            {cycle values="even,odd" assign=class}
        {/if}
        <tr class="{$class}" style="{if $op.isCanceled==1}text-decoration: line-through;{/if}">
            {if isset($op.bill) && $op.bill}
                <td rowspan="{$rowspan}"{if $op.bill.postreg!="0000-00-00"} style="background-color: #FFFFD0"{/if}>{$op.bill.bill_date}</td>
                <td rowspan="{$rowspan}" class="pay{$op.bill.is_payed}">
                    <a href="{$LINK_START}module=newaccounts&action=bill_view&bill={$op.bill.bill_no}">{$op.bill.bill_no}</a>
                </td>
                <td rowspan="{$rowspan}" align="right">{$op.bill.sum|money:$op.bill.currency}</td>
            {else}
                <td colspan="3" rowspan="{$rowspan}">&nbsp;</td>
            {/if}
            <td rowspan="{$rowspan}" align="right">
                {if $fixclient_data.currency == $op.bill.currency}
                    {objCurrency op=$op obj='delta' currency=$fixclient_data.currency simple=1}
                {/if}
            </td>
            {if count($op.pays)}
                {foreach from=$op.pays item=pay key=keyin name=inner}
                    {if $smarty.foreach.inner.iteration!=1}
                        </tr><tr class="{$class}">
                    {/if}
                    <td>
                        {objCurrency op=$op obj='pay' pay=$pay currency=$fixclient_data.currency simple=1}
                    </td>
                    <td style="font-size: 85%;">
                        {$pay.payment_date} - &#8470;{$pay.payment_no} /
                        {if $pay.type=='bank'}b({$pay.bank})
                        {elseif $pay.type=='prov'}p
                        {elseif $pay.type=='neprov'}n
                        {elseif $pay.type=='webmoney'}wm
                        {elseif $pay.type=='yandex'}y
                        {else}{$pay.type}{/if}
                        {if $pay.oper_date!="0000-00-00"} - {$pay.oper_date}{/if}
                    </td>
                    <td style="padding:0 0 0 0;">{if isset($op.bill) && $op.bill.currency=='USD'}{$pay.payment_rate}{else}&nbsp;{/if}</td>
                    <td><span title="{$pay.add_date}">{$pay.user_name}</span></td>
                    {if $pay.comment}
                        </tr>
                        <tr class="{$class}">
                            <td colspan="4" class="comment">{$pay.comment|escape:"html"}</td>
                    {/if}
                {/foreach}
                {if $op.bill.comment}
                    </tr>
                    <tr class="{$class}">
                        <td colspan="4" class="comment">{$op.bill.comment|strip_tags}</td>
                        <td colspan="4">&nbsp;</td>
                {/if}
            {else}
                {if isset($op.bill.comment) && $op.bill.comment}
                        <td colspan="4" rowspan="2">&nbsp;</td>
                    </tr>
                    <tr class="{$class}">
                        <td colspan="4" class="comment">{$op.bill.comment|strip_tags}</td>
                {else}
                    <td colspan="4">&nbsp;</td>
                {/if}
            {/if}
        </tr>
        {if isset($op.switch_to_mcn) && $op.switch_to_mcn}
            <tr>
                <td colspan="12" style="padding:0 0 0 0;margin: 0 0 0 0;background-color: #9edbf0; font-size: 8pt; text-align: center;">{$op.switch_to_mcn}</td>
            </tr>
        {/if}
    {/foreach}
</table>

<script type="text/javascript">
    {literal}
        $( '#date').datepicker({dateFormat: 'yy-mm-dd'});
    {/literal}
</script>