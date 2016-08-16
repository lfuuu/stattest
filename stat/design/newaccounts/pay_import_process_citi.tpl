{assign var='isSberObline' value=7707083893}

<h2>Импорт платежей</h2>
<form action="?" method="POST">
<input type="hidden" name="module" value="newaccounts">
<input type="hidden" name="action" value="pi_apply">
<input type="hidden" name="file" value="{$file}">
<input type="hidden" name="bank" value="citi">
<input type="checkbox" id="check_all" style="margin-left:4px;" onClick="optools.pays.sel_all_pay_radio(event)" /> Выбрать всех<br />

<table class="price" cellspacing="4" cellpadding="2" width="100%" border="0" id="pays_tbl">
    {foreach from=$pays item=pay name=outer}
        <tr bgcolor="#FFFFF5">
            <td colspan="4">
                {if $pay.inn == $isSberObline}
                    <b><span class="accounts-show" data-pay-no="{$pay.no}">Показать всех</span> / <span class="accounts-hide" data-pay-no="{$pay.no}">Скрыть</span></b><br />
                {/if}

                {if $pay.clients}
                    {foreach from=$pay.clients item=client}
                        <div class="accounts" data-pay-no="{$pay.no}" data-is-sber-online="{if $pay.inn == $isSberObline}1{else}0{/if}">
                            <input type="radio" name="pay[{$pay.no}][client]" data-pay-no="{$pay.no}" data-client-account-id="{$client.id}" value="{$client.client}"{if (isset($pay.imported) && $pay.imported) || (isset($pay.to_check_bill_only) && $pay.to_check_bill_only)} disabled="disabled"{/if} />
                            <a href="./?module=newaccounts&action=bill_list&clients_client={$client.id}">
                                {$client.id} <small>({$client.client})</small> <font style="color:green;"> ({$client.currency})</font>
                            </a> - <span style="font-size:85%">{$client.full_name} ({$client.manager})</span>
                        </div>
                    {/foreach}
                {/if}

                {if !isset($pay.imported) || !$pay.imported}
                    <input type="radio" name="pay[{$pay.no}][client]" value="" />не вносить
                {/if}
            </td>
        </tr>
        <tr bgcolor="{if isset($pay.imported) && $pay.imported}#FFE0E0{else}#EEDCA9{/if}">
            <td>
                {if $pay.sum > 0}<br /><br />{/if}
                Платеж &#8470;{$pay.noref} от {$pay.date}
                {if $pay.inn}<br /><span style="color: #AAA;">ИНН {$pay.inn}</span>{/if}
                {if isset($pay.to_check) && $pay.to_check}<div style="color:#C40000;font: bold 8pt sans-serif;">Внимание! Компания платильшик и компания, вледелец счета не совпадаю!</div>{/if}
                {if isset($pay.to_check_bill_only) && $pay.to_check_bill_only}<br /><br /><div style="color:#C40000;font: bold 8pt sans-serif;">Внимание! Компания&nbsp;найдена&nbsp;по&nbsp;счету</div>{/if}
                {if !$pay.clients || (isset($pay.to_check_bill_only) && $pay.to_check_bill_only)}
                    {if !isset($pay.to_check_bill_only) || !$pay.to_check_bill_only}<br /><br />{/if}
                    <span style="color: gray;">р/с: {$pay.from.account} <br />бик: {$pay.from.bik}</span>
                {/if}
                <br /><br /><br /><span style="font-size:7pt;" title="{$pay.company|escape}">{$pay.company|truncate:35}</span>
                <input type="hidden" name="pay[{$pay.no}][pay]" value="{$pay.noref}" />
                <input type="hidden" name="pay[{$pay.no}][date]" value="{$pay.date}" />
                <input type="hidden" name="pay[{$pay.no}][oper_date]" value="{$pay.oper_date}" />
                <input type="hidden" name="pay[{$pay.no}][sum]" value="{$pay.sum}" />
            </td>
            <td><b>{$pay.sum}</b> р.</td>
            <td>
                {if $pay.clients}
                    <select name="pay[{$pay.no}][bill_no]" data-pay-no="{$pay.no}" id="bills_{$pay.no}"{if isset($pay.to_check_bill_only) && $pay.to_check_bill_only} disabled="disabled"{/if}>
                        <option value=''>(без привязки)</option>
                        {assign var='is_select' value=false}
                        {foreach from=$pay.clients_bills item=bill name=inner2}
                            {if isset($bill.is_group) && $bill.is_group}
                                </optgroup>
                                <optgroup label="{$bill.bill_no}">
                            {else}
                                <option value="{$bill.bill_no}" data-client-account-id="{$bill.client_id}"{if $bill.is_selected} selected{assign var='is_select' value=true}{/if}>
                                    ЛС {$bill.client_id} # {$bill.bill_no}
                                    {if $bill.is_payed==1}
                                        =
                                    {elseif $bill.is_payed ==2}
                                        +
                                    {elseif $bill.is_payed ==-1}
                                        -
                                    {/if}
                                    {if isset($bill.ext_no) && $bill.ext_no}
                                        {$bill.ext_no}
                                        {if $bill.bill_no_ext_date}
                                            ({"d-m-Y"|date:$bill.bill_no_ext_date})
                                        {/if}
                                    {/if}
                                </option>
                            {/if}
                        {/foreach}
                        {if !$is_select && $pay.bill_no}
                            </optgroup>
                            <optgroup label="Вне списка">
                                <option value="{$pay.bill_no}" selected>{$pay.bill_no}{if !isset($pay.imported) || !$pay.imported} !?{/if} ??</option>
                            </optgroup>
                        {/if}
                    </select>
                {else}
                    <input type="text" class="text" name="pay[{$pay.no}][bill_no]" style="width:100px" />
                {/if}
                <input type="text" class="text" name="pay[{$pay.no}][usd_rate]" style="width:60px" value="{if isset($pay.usd_rate)}{$pay.usd_rate}{/if}" />
                {if $pay.clients && !$is_select && $pay.bill_no && (!isset($pay.imported) || !$pay.imported)}<div style="color:#c40000; font: bold 8pt sans-serif;">Внимание!!! Счет в комментариях не найден в счетах клиентов.</div>{/if}
            </td>
            <td width=50%>
                {$pay.description|escape:"html"}<br />
                    <textarea name="pay[{$pay.no}][comment]" class="text" style="width:100%;font-size:85%">{if isset($pay.comment)}{$pay.comment}{/if}</textarea>
            </td>
        </tr>
    {/foreach}
</table>

<br /><b>Сумма занесенных: </b>{$sum.imported} руб.<br />
<br /><b>Сумма +/-: </b>{$sum.plus} / {$sum.minus} руб.<br />
<br /><b>Сумма итого: </b>{$sum.all} руб.<br />
<div align="center"><input class="button" type="submit" value="Внести платежи"></div>
</form>

{if $bills}
    <hr />
    Печать сопроводительного письма, счета и акт-1(2):

    {assign var="cc" value=0}
    {assign var="cp" value=1}
    <table border=0>
        <tr>
            <td>
                <form  style="padding: 0; margin: 0;">
                {foreach from=$bills item=bill_no}
                    {if $bill_no}
                        {if $cc%10 == 0}
                            </form>
                            </td><td>
                            <form action="./?module=newaccounts&bill-2-RUB=1&envelope=1&action=bill_mprint&akt-1=1&from=import" method=post target=_blank><input type="submit" value="{$cp}" style="padding: 5px;width: 50px;" onclick="this.style.background='#E0FFE0';">
                            {assign var="cp" value=$cp+1}
                        {/if}
                        <input type=hidden name=bill[] value="{$bill_no}">{assign var="cc" value=$cc+1}{/if}
                {/foreach}
                </form>
            </td>
            <td>
                <form action="./?module=newaccounts&bill-2-RUB=1&envelope=1&action=bill_mprint&akt-1=1&from=import&one_pdf=1" method="POST" target="_blank">
                {foreach from=$bills item=bill_no}
                    <input type="hidden" name="bill[]" value="{$bill_no}" />
                {/foreach}
                <input type="submit" value="PDF одним файлом" style="padding: 5px;" />
                </form>
            </td>
        </tr>
    </table>

    <!--form action="./?module=newaccounts&invoice-1=1&action=bill_mprint&from=import" method=post-->

    <hr />
    Печать с/ф-1:
    {assign var="cc" value=0}
    {assign var="cp" value=1}
    <table border="0">
        <tr>
            <td>
                <form  style="padding: 0; margin: 0;">
                {foreach from=$bills item=bill_no}
                    {if $bill_no}
                        {if $cc%10 == 0}
                            </form></td><td>
                            <form action="./?module=newaccounts&invoice-1=1&action=bill_mprint&from=import" method="POST" target="_blank">
                            <input type="submit" value="{$cp}" style="padding: 5px;width: 50px;" onclick="this.style.background='#E0FFE0';">
                            {assign var="cp" value=$cp+1}
                        {/if}
                        <input type="hidden" name="bill[]" value="{$bill_no}">{assign var="cc" value=$cc+1}
                    {/if}
                {/foreach}
                </form>
            </td>
            <td>
                <form action="./?module=newaccounts&invoice-1=1&action=bill_mprint&from=import&one_pdf=1" method="POST" target="_blank">
                {foreach from=$bills item=bill_no}
                    <input type="hidden" name="bill[]" value="{$bill_no}" />
                {/foreach}
                <input type="submit" value="PDF одним файлом" style="padding: 5px;" />
                </form>
            </td>
        </tr>
    </table>

    <hr />
    Печать сопроводительного письма, счета:
    {assign var="cc" value=0}
    {assign var="cp" value=1}
    <table border="0">
        <tr>
            <td>
                <form  style="padding: 0; margin: 0;">
                {foreach from=$bills item=bill_no}
                    {if $bill_no}
                        {if $cc%10 == 0}
                            </form></td><td>
                            <form action="./?module=newaccounts&bill-2-RUB=1&envelope=1&action=bill_mprint&from=import" method="POST" target="_blank">
                            <input type="submit" value="{$cp}" style="padding: 5px;width: 50px;" onclick="this.style.background='#E0FFE0';" />
                            {assign var="cp" value=$cp+1}
                        {/if}
                        <input type="hidden" name="bill[]" value="{$bill_no}">{assign var="cc" value=$cc+1}
                    {/if}
                {/foreach}
                </form>
            </td>
            <td>
                <form action="./?module=newaccounts&bill-2-RUB=1&envelope=1&action=bill_mprint&from=import&one_pdf=1" method="POST" target="_blank">
                {foreach from=$bills item=bill_no}
                    <input type="hidden" name="bill[]" value="{$bill_no}" />
                {/foreach}
                <input type="submit" value="PDF одним файлом" style="padding: 5px;" />
                </form>
            </td>
        </tr>
    </table>

    <hr />
    Печать УПД:
    {assign var="cc" value=0}
    {assign var="cp" value=1}
    <table border="0">
        <tr>
            <td>
                <form  style="padding: 0; margin: 0;">
                {foreach from=$bills item=bill_no}
                    {if $bill_no}
                        {if $cc%10 == 0}
                            </form></td><td>
                            <form action="./?module=newaccounts&upd-1=1&upd-2=1&action=bill_mprint&from=import" method="POST" target="_blank">
                            <input type="submit" value="{$cp}" style="padding: 5px;width: 50px;" onclick="this.style.background='#E0FFE0';" />
                            {assign var="cp" value=$cp+1}
                        {/if}
                        <input type="hidden" name="bill[]" value="{$bill_no}">{assign var="cc" value=$cc+1}
                    {/if}
                {/foreach}
                </form>
            </td>
            <td>
                <form action="./?module=newaccounts&upd-1=1&action=bill_mprint&from=import&one_pdf=1" method="POST" target="_blank">
                {foreach from=$bills item=bill_no}
                    <input type="hidden" name="bill[]" value="{$bill_no}" />
                {/foreach}
                <input type="submit" value="PDF одним файлом" style="padding: 5px;">
                </form>
            </td>
        </tr>
    </table>

    <hr />
    Регистрация почтового реестра:
    {assign var="cc" value=0}
    {assign var="cp" value=1}
    <table border="0">
        <tr>
            <td>
                <form  style="padding: 0; margin: 0;">
                {foreach from=$bills item=bill_no}
                    {if $bill_no}
                        {if $cc%10 == 0}
                            </form></td><td>
                            <form action="./?module=newaccounts&action=bill_postreg&from=import" method="POST" target="_blank">
                            <input type="submit" value="{$cp}" style="padding: 5px;width: 50px;" onclick="this.style.background='#E0FFE0';" />
                            {assign var="cp" value=$cp+1}
                        {/if}
                        <input type="hidden" name="bill[]" value="{$bill_no}">{assign var="cc" value=$cc+1}
                    {/if}
                {/foreach}
                </form>
            </td>
        </tr>
    </table>
{/if}

<script type="text/javascript">
{literal}
    jQuery(document).ready(function() {
        $('div[data-is-sber-online="1"]').hide();

        $('span.accounts-show').each(function() {
            var $that = $(this);

            $(this).wrap($('<a />').attr('href', 'javascript:void(0)').on('click', function() {
                $('div[data-pay-no="' + $that.data('pay-no') + '"]').show();
            }));
        });

        $('span.accounts-hide').each(function() {
            var $that = $(this);
            $(this).wrap($('<a />').attr('href', 'javascript:void(0)').on('click', function() {
                $('div[data-pay-no="' + $that.data('pay-no') + '"]')
                        .hide()
                        .find('input:checked')
                        .parent('div')
                            .show();
            }));
        });

        $('select[data-pay-no]').on('change', function() {
            var $clients = $('div[data-pay-no="' + $(this).data('pay-no') + '"]'),
                clientAccountId = $(this).find('option:selected').data('client-account-id');

            $clients
                .find('input[data-client-account-id="' + clientAccountId + '"]:not(:disabled)')
                    .prop('checked', true);

            $('span.accounts-hide[data-pay-no="' + $(this).data('pay-no') + '"]')
                .parent('a')
                    .trigger('click');
        }).trigger('change');

        $('input[data-client-account-id]').on('change', function() {
            var $selectBox = $('select[data-pay-no="' + $(this).data('pay-no') + '"]');
                current = $selectBox.find('option[data-client-account-id="' + $(this).data('client-account-id') + '"]:selected');

            if (!current.length) {
                $selectBox
                    .find('option[data-client-account-id="' + $(this).data('client-account-id') + '"]')
                        .prop('selected', true);
            }

            $('span.accounts-hide[data-pay-no="' + $(this).data('pay-no') + '"]')
                .parent('a')
                    .trigger('click');
        });
    });
{/literal}
</script>