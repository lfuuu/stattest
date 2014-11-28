{if isset($tt_folders_block)}{$tt_folders_block}{/if}

{if isset($premain)}
{foreach from=$premain item=item}
    {if $item[0] == 0}{$item[1]}{else}{include file="$item[1]"}{/if}
{/foreach}
{/if}

{if isset($main)}
{foreach from=$main item=item}
    {if $item[0] == 0}{$item[1]}{else}{include file="$item[1]"}{/if}
{/foreach}
{/if}
