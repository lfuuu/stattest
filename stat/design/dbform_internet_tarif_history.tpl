<h3>История тарифов:</h3>
{if count($dbform_f_tarif)}{foreach from=$dbform_f_tarif item=T key=key name=tarif_1}
{if $T.is_current}<span title='текущий{if $T.is_previous}, предыдущий{/if}' style='color:#0000C0'>{elseif $T.is_previous}<span title='предыдущий' style='color:#C00000'>{elseif $T.is_next}<span style='color:#00C000' title='следующий'>{/if}
<b>{$T.ts|udate} - {$T.user}</b>: смена на тариф {$T.name} ({$T.mb_month}-{$T.pay_month}-{$T.pay_mb}), дата активации {$T.date_activation}<br>
{if $T.is_current || $T.is_previous || $T.is_next}</span>{/if}
{if $T.comment}<b>Комментарий: </b>	{$T.comment}<br><br>{/if}
{/foreach}{else}тариф ни разу не был выбран{/if}
<script language=javascript>form_ip_ports_hide(1);</script>
