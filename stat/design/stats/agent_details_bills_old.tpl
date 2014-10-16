<h3>Информация по оплаченным счетам клиента <br/>
<a target="_blank" style="font-size: 20px; color: blue;" href="?module=newaccounts&clients_client={$title.client_id}">{$title.title}</a><br/>
{$title.period}</h3>
<br/>
<table class="price" width=100%>
    {assign var="payment" value=""}
    {assign var="bill" value=""}
    <tr>
        <th class="header">ID платежа</th>
        <th class="header">Дата</th>
        <th class="header">Сумма</th>
    </tr>
    {if $data}
        {foreach from=$data item="d" name="outer"}
            {if $payment != $d.id}
                {if $payment}
                    <tr>
                        <td colspan=3><hr/></td>
                    </tr>
                {/if}
                {assign var="payment" value=$d.id}
                {assign var="bill" value=""}
                <tr style="background-color: #FFFFCC; font-weight: bold; font-size: 16px;">
                    <td>
                        {$d.id}
                    </td>
                    <td>
                        {$d.ts|mdate:"d месяца Y г"}
                    </td>
                    <td align=right>
                        {$d.sum_rub|num_format:"true":"2"} p.
                    </td>
                </tr>
                
            {/if}
            {if $bill != $d.bill_no}
                {assign var="bill" value=$d.bill_no}
                <tr style="background-color: #CCFFFF; font-weight: bold; font-size: 14px;">
                    <td colspan=2 align=center>
                        Счет {$bill}
                    </td>
                    <td align=right>
                        {$d.p_sum|num_format:"true":"2"} p.
                    </td>
                </tr>
            {/if}
            <tr  {if !$d.is_tel}style="color: #CCCCCC;"{/if}>
                <td>&nbsp;</td>
                <td>{$d.item}</td>
                <td align=right>{$d.sum|num_format:"true":"2"} p.</td>
            </tr>
        {/foreach}
        <tr><td colspan=3 style="color: black;"><hr/></td></tr>
        <tr>
                <td colspan=2 align="right">
                        <b>Всего платежей</b>
                </td>
                <td align="right">
                        <b>{$totals.bills_all|num_format:"true":"2"} p.</b>
                </td>
        </tr>
        <tr>
                <td colspan=2 align="right">
                        <b>Сумма позиций не относящиеся к телефонии</b>
                </td>
                <td align="right">
                        <b>{$totals.no_tel_sum|num_format:"true":"2"} p.</b>
                </td>
        </tr>
        <tr>
                <td colspan=2 align="right">
                        <b>Итого</b>
                </td>
                <td align="right">
                        <b>{$totals.bills_all-$totals.no_tel_sum|num_format:"true":"2"} p.</b>
                </td>
        </tr>
    {else}
        <tr>
                <td colspan="4">Нет данных</td>
        </tr>
    {/if}
</table>