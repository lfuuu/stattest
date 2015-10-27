<h3>История тарифов:</h3>
{if isset($dbform_f_tarif) && count($dbform_f_tarif)}
	{foreach from=$dbform_f_tarif item=T key=key name=tarif_1}
	{if $T.is_current}<span style='color:#0000C0'>{elseif $T.is_previous}<span style='color:#C00000'>{elseif $T.is_next}<span style='color:#00C000'>{/if}
	<b>{$T.ts|udate_with_timezone} - {$T.user}</b>:
	{$T.name} ({$T.month_number}-{$T.month_line}), дата активации {$T.date_activation} {$T.tarif_local_mob_name}
		{if $T.dest_group != 0}
		/ Набор:
		{if strpos($T.dest_group, '5') !== false}Моб{/if}
		{if strpos($T.dest_group, '1') !== false}МГ{/if}
		{if strpos($T.dest_group, '2') !== false}МН{/if}
		({$T.minpayment_group})
		{/if}
		{if strpos($T.dest_group, '5') === false}
		/ Моб {$T.tarif_local_mob_name} {if $T.minpayment_local_mob > 0}({$T.minpayment_local_mob}){/if}
		{/if}
		{if strpos($T.dest_group, '1') === false}
		/ МГ {$T.tarif_russia_name} {if $T.minpayment_russia > 0}({$T.minpayment_russia}){/if}
		{/if}
		{if strpos($T.dest_group, '1') === false}
		/ МГ {$T.tarif_russia_mob_name}
		{/if}
		{if strpos($T.dest_group, '2') === false}
		/ МН {$T.tarif_intern_name} {if $T.minpayment_intern > 0}({$T.minpayment_intern}){/if}
		{/if}
	<br>
	{if $T.is_current || $T.is_previous || $T.is_next}</span>{/if}
	{if $T.comment}<b>Комментарий: </b>	{$T.comment}<br><br>{/if}
{/foreach}
{else}тариф ни разу не был выбран{/if}
<h3>История тарификации:</h3>
{if isset($dbform_f_tarif2) && count($dbform_f_tarif2)}
	{foreach from=$dbform_f_tarif2 item=T key=key name=tarif_1}
	{if $T.is_current}<span style='color:#0000C0'>{elseif $T.is_previous}<span style='color:#C00000'>{elseif $T.is_next}<span style='color:#00C000'>{/if}
	<b>{$T.ts|udate_with_timezone} - {$T.user}</b>: смена на группу {$T.id_tarif}, дата активации {$T.date_activation}<br>
	{if $T.is_current || $T.is_previous || $T.is_next}</span>{/if}
	{if $T.comment}<b>Комментарий: </b>	{$T.comment}<br><br>{/if}
	{/foreach}
{else}группа тарификации ни разу не была выбрана{/if}
