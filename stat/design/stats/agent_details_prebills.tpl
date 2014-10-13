<h3>Информация по оплаченным счетам клиента <br/>
<a target="_blank" style="font-size: 20px; color: blue;" href="?module=newaccounts&clients_client={$title.client_id}">{$title.title}</a><br/>
{$title.period}</h3>
<br/>
<table class="price" width=100%>
    {assign var="bill" value=""}
    <tr>
        <th class="header">Счет</th>
        <th class="header">Дата</th>
        <th class="header">Сумма</th>
    </tr>
    {if $data}
        {foreach from=$data item="d" name="outer"}
            {if $bill != $d.bill_no}
                {assign var="bill" value=$d.bill_no}
                <tr style="background-color: #CCFFFF; font-weight: bold; font-size: 14px;">
                    <td>
                        {$bill}
                    </td>
                    <td>
                        {$d.ts|mdate:"d месяц Y г"}
                        {if $d.is_payed != 1}
                            <span style="color: red;">Не оплачен</span>
                        {/if}
                    </td>
                    <td align=right>
                        <span {if $d.is_payed != 1}style="text-decoration: line-through;"{/if}>{$d.b_sum|num_format:"true":"2"} p.</span>
                    </td>
                </tr>
            {/if}
            <tr  {if !$d.is_abon}style="color: #CCCCCC;"{/if}>
                <td>&nbsp;</td>
                <td>{$d.item}</td>
                <td align=right>{$d.sum|num_format:"true":"2"} p.</td>
            </tr>
        {/foreach}
        <tr><td colspan=3 style="color: black;"><hr/></td></tr>
        <tr>
                <td colspan=2 align="right">
                        <b>Всего оплаченно счетов на сумму</b>
                </td>
                <td align="right">
                        <b>{$totals.bills|num_format:"true":"2"} p.</b>
                </td>
        </tr>
        <tr>
                <td colspan=2 align="right">
                        <b>Сумма позиций не относящиеся к телефонии или не являющиеся абоненсткой платой</b>
                </td>
                <td align="right">
                        <b>{$totals.bills-$totals.prebills|num_format:"true":"2"} p.</b>
                </td>
        </tr>
        <tr>
                <td colspan=2 align="right">
                        <b>Итого</b>
                </td>
                <td align="right">
                        <b>{$totals.prebills|num_format:"true":"2"} p.</b>
                </td>
        </tr>
    {else}
        <tr>
                <td colspan="4">Нет данных</td>
        </tr>
    {/if}
</table>