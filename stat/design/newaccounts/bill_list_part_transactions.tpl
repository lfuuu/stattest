<br/>
{if $notLinkedtransactions}
    <div><a onclick="$('#transactions').toggle(); return false;" href="#">Не привязанные к счету транзакции</a></div>
    <div id="transactions" style="display: none">
        <TABLE class=price cellSpacing=3 cellPadding=1 border=0 width=100%>
            <TR>
                <TD class=header vAlign=bottom>Период</TD>
                <TD class=header vAlign=bottom>Наименование</TD>
                <TD class=header vAlign=bottom>Количество</TD>
                <TD class=header vAlign=bottom>Сумма</TD>
                <TD class=header vAlign=bottom>Сумма (пропорционально)</TD>
            </TR>
            {foreach from=$notLinkedtransactions item=transaction}
                <TR class="{cycle values="even,odd"}">
                    <TD>{$transaction.transaction_date}</TD>
                    <TD>{$transaction.name}</TD>
                    <TD>{$transaction.amount}</TD>
                    <TD>{$transaction.sum}</TD>
                    <TD>{$transaction.effective_sum-$transaction.effective_sum-$transaction.effective_sum}</TD>
                </TR>
            {/foreach}
        </TABLE>
    </div>
    <br/>
{/if}