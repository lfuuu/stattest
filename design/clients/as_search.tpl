{if is_array($clients)}
<table border=0 cellspacing=0 cellpadding=1 style="border: 1px solid gray;background-color: white; padding: 10px 10px 10px 10px;width: 600px;">
{foreach from=$clients item=item name=outer}
{cycle values="#f3f3f3,#ffffff" assign=search_td_oe}
<tr style="background-color:{$search_td_oe} ;" onmouseover='this.style.backgroundColor="#B5FFA3"' onmouseout='this.style.backgroundColor="{$search_td_oe}"'>
<td width=100><a href='{$LINK_START}module=clients&id={$item.id}'>{if $item.client==""}Заявка {$item.id|hl:$search}{else}{$item.client|hl:$search}{/if}</a></td>
<td>{if strlen($item.company_full)>strlen($item.company)}{$item.company_full|hl:$search}{else}{$item.company|hl:$search}{/if}</td>
</tr>{/foreach}{/if}
