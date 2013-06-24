{if count($dbform_f_block)}<h3>История блокировок/изменений:</h3>{/if}
{foreach from=$dbform_f_block item=T key=key name=tarif_1}
<b>{$T.ts} - {$T.user}</b>: {if $T.block}блокировка{else}разблокировка{/if}{if $T.fields_changes} изменения полей: {$T.fields_changes} {/if}{if $T.comment} с комментарием {$T.comment}{/if}<br>
{/foreach}
