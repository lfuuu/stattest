<h2>Импорт платежей</h2>
<form action='?' method=post>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=pi_apply>
<input type=hidden name=file value='{$file}'>
<input type=hidden name=bank value='citi'>
<input type='checkbox' id='check_all' style='margin-left:4px' onclick='optools.pays.sel_all_pay_radio(event)'> Выбрать всех<br />
<TABLE class=price cellSpacing=4 cellPadding=2 width=100% border=0 id="pays_tbl">
{foreach from=$pays item=pay name=outer}
<tr bgcolor=#fffff5><td colspan='4'>
    {if $pay.clients}
        {foreach from=$pay.clients item=client}
 <input type=radio name=pay[{$pay.no}][client] value='{$client.client}'{if $pay.imported || $pay.to_check_bill_only} disabled='disabled'{/if}>
                <a href='./?module=newaccounts&action=bill_list&clients_client={if $client.client}{$client.client|escape:'url'}{else}{$client.id}{/if}'>{$client.client}{if $client.currency == "USD"}<font style="color:green;"> ($)</font>{/if}</a> -
                <span style='font-size:85%'>{$client.full_name} ({$client.manager})
                </span><br>
        {/foreach}
    {/if}
	{if !$pay.imported}
		<input type=radio name=pay[{$pay.no}][client] value=''>не вносить
	{/if}
</td>
</tr>
<tr bgcolor={if $pay.imported}#FFE0E0{else}#EEDCA9{/if}><td>{if $pay.sum > 0}<br><br>{/if}Платеж &#8470;{$pay.noref} от {$pay.date}
{if $pay.inn}<br><span style="color: #aaa;">ИНН {$pay.inn}</span>{/if}
    {if $pay.to_check}<div style="color:#c40000;font: bold 8pt sans-serif;">Внимание! Компания платильшик и компания, вледелец счета не совпадаю!</div>{/if}
    {if $pay.to_check_bill_only}<br><br><div style="color:#c40000;font: bold 8pt sans-serif;">Внимание! Компания&nbsp;найдена&nbsp;по&nbsp;счету</div>{/if}
    {if !$pay.clients || $pay.to_check_bill_only}
{if !$pay.to_check_bill_only}<br/><br/>{/if}<span style="color: gray;">р/с: {$pay.from.account}
        <br/>бик: {$pay.from.bik}</span>
    {/if}
<br><br><br><span style="font-size:7pt;" title="{$pay.company|escape}">{$pay.company|truncate:35}</span>
<input type=hidden name=pay[{$pay.no}][pay] value='{$pay.noref}'>
<input type=hidden name=pay[{$pay.no}][date] value='{$pay.date}'>
<input type=hidden name=pay[{$pay.no}][oper_date] value='{$pay.oper_date}'>
<input type=hidden name=pay[{$pay.no}][sum_rub] value='{$pay.sum}'></td>
<td><b>{$pay.sum}</b> р.</td><td>

{if $pay.clients}
	<select name=pay[{$pay.no}][bill_no] id=bills_{$pay.no}{if $pay.to_check_bill_only} disabled='disabled'{/if}>
	<option value=''>(без привязки)</option>
    {assign var='is_select' value=false}
	{foreach from=$pay.clients_bills item=bill name=inner2}
        {if $bill.is_group}
            </optgroup>
            <optgroup label="{$bill.bill_no}">
        {else}
            <option value={$bill.bill_no}{if $pay.bill_no==$bill.bill_no || ($pay.sum < 0 && $bill.sum == $pay.sum)} selected{assign var='is_select' value=true}{/if}>{$bill.bill_no}{if $bill.is_payed==1}={elseif $bill.is_payed ==2}+{elseif $bill.is_payed ==-1}-{/if}</option>
        {/if}
	{/foreach}
	{if !$is_select && $pay.bill_no}
        </optgroup>
        <optgroup label="Вне списка">
            <option value={$pay.bill_no} selected>{$pay.bill_no}{if !$pay.imported} !?{/if} ??</option>
        </optgroup>
    {/if}
	</select>
{else}
	<input type=text class=text name=pay[{$pay.no}][bill_no] style='width:100px'>
{/if}
<input type=text class=text name=pay[{$pay.no}][usd_rate] style='width:60px' value={$pay.usd_rate}>
{if $pay.clients && !$is_select && $pay.bill_no && !$pay.imported}<div style="color:#c40000; font: bold 8pt sans-serif;">Внимание!!! Счет в комментариях не найден в счетах клиентов.</div>{/if}
</td><td width=50%>
{$pay.description|escape:"html"}<br>
<textarea name=pay[{$pay.no}][comment] class=text style='width:100%;font-size:85%'>{$pay.comment}</textarea>
</td></tr>
{/foreach}
</TABLE>
<br><b>Сумма занесенных: </b>{$sum.imported} руб.<br>
<br><b>Сумма +/-: </b>{$sum.plus} / {$sum.minus} руб.<br>
<br><b>Сумма итого: </b>{$sum.all} руб.<br>
<DIV align=center><INPUT class=button type=submit value="Внести платежи"></DIV></FORM>

{if $bills}
<hr>
Печать сопроводительного письма, счета и акт-1(2):

{assign var="cc" value=0}
{assign var="cp" value=1}
<table border=0>
<tr>
<td>
<form  style="padding: 0; margin: 0;">
{foreach from=$bills item=bill_no}
{if $bill_no}{if $cc%10 == 0}</form></td><td>
	<form action="./?module=newaccounts&bill-2-RUR=1&envelope=1&action=bill_mprint&akt-1=1&from=import" method=post target=_blank><input type="submit" value="{$cp}" style="padding: 0; margin: 0;">
	{assign var="cp" value=$cp+1}
	{/if}<input type=hidden name=bill[] value="{$bill_no}">{assign var="cc" value=$cc+1}{/if}
{/foreach}
</form>
</td></tr></table>




<!--form action="./?module=newaccounts&invoice-1=1&action=bill_mprint&from=import" method=post-->


<hr>
Печать с/ф-1:
{assign var="cc" value=0}
{assign var="cp" value=1}
<table border=0>
<tr>
<td>
<form  style="padding: 0; margin: 0;">
{foreach from=$bills item=bill_no}
{if $bill_no}{if $cc%10 == 0}</form></td><td>
	<form action="./?module=newaccounts&invoice-1=1&action=bill_mprint&from=import" method=post target=_blank><input type="submit" value="{$cp}" style="padding: 0; margin: 0;">
	{assign var="cp" value=$cp+1}
	{/if}<input type=hidden name=bill[] value="{$bill_no}">{assign var="cc" value=$cc+1}{/if}
{/foreach}
</form>
</td></tr></table>

<hr>
Регистрация почтового реестра:
{assign var="cc" value=0}
{assign var="cp" value=1}
<table border=0>
<tr>
<td>
<form  style="padding: 0; margin: 0;">
{foreach from=$bills item=bill_no}
{if $bill_no}{if $cc%10 == 0}</form></td><td>
	<form action="./?module=newaccounts&action=bill_postreg&from=import" method=post target=_blank><input type="submit" value="{$cp}" style="padding: 0; margin: 0;">
	{assign var="cp" value=$cp+1}
	{/if}<input type=hidden name=bill[] value="{$bill_no}">{assign var="cc" value=$cc+1}{/if}
{/foreach}
</form>
</td></tr></table>
{/if}

