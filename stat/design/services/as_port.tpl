{if is_array($data)}{foreach from=$data item=item name=outer}
<option value="{$item.port}">{$item.port}</option>
{/foreach}{/if} 