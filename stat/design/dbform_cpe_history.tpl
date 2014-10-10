{if count($dbform_f_cpe)}<h3>История клиентских устройств:</h3>{/if}
{foreach from=$dbform_f_cpe item=cpe key=key name=tarif_1}
<b>{$cpe.actual_from} - {$cpe.actual_to}</b>: {$cpe.vendor} {$cpe.model} {$cpe.serial} {$cpe.ip}<br>
{/foreach}