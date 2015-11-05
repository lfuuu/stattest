<h3>История изменений:</h3>
{if count($dbform_f_history)}{foreach from=$dbform_f_history item=L key=key name=hist}
<b>{$L.ts|udate_with_timezone} - {$L.user}</b><br>
{/foreach}{/if}