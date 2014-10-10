<h2>Массовая печать счетов</h2>
{foreach from=$pages item=page}
<a href='?module=newaccounts&action=bill_mass&obj=print&page={$page}&do_bill={$do_bill}&do_inv={$do_inv}&do_akt={$do_akt}&date={$date}' target=_blank>Часть {$page}</a><br>
{/foreach}
