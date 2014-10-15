{foreach from=$premain item=item}
    {if $item[0] == 0}{$item[1]}{else}{include file="$item[1]"}{/if}
{/foreach}

{foreach from=$main item=item}
    {if $item[0] == 0}{$item[1]}{else}{include file="$item[1]"}{/if}
{/foreach}