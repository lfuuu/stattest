<h3>История тарифов:</h3>
{if count($dbform_f_tarif)}{foreach from=$dbform_f_tarif item=T key=key name=tarif_1}
{if $T.is_current}<span style='color:#0000C0'>{elseif $T.is_previous}<span style='color:#C00000'>{elseif $T.is_next}<span style='color:#00C000'>{/if}
<b>{$T.ts} - {$T.user}</b>: смена на тариф {$T.name} ({$T.pay_month}$-{$T.mb_disk}MB), дата активации {$T.date_activation}
{$tarif_sng_name}
<br>
{if $T.is_current || $T.is_previous || $T.is_next}</span>{/if}
{if $T.comment}<b>Комментарий: </b>	{$T.comment}<br><br>{/if}
{/foreach}{else}тариф ни разу не был выбран{/if}
