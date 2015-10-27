<h3>История изменений сети:</h3>
{if count($dbform_f_route)}{foreach from=$dbform_f_route item=R key=key name=route_1}
<b>{$R.ts|udate_with_timezone} - {$R.user}</b>: Сеть {$R.net}, период {$R.actual_from} - {$R.actual_to}<br>
{/foreach}{/if}
