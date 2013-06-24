<H2>Мониторинг</H2>
<H3>IP-адрес {$ip}</H3>
<img src='img_stat.php?ip={$ip}&period={$period}&skip={$skip}&year={$year}&month={$month}&day={$day}'><br>
<br>
Показать: {if $period!=0}<a href='{$LINK_START}module=monitoring&ip={$ip}&period=0'>за последние сутки</a>{else}за последние сутки{/if} {foreach from=$years item=item}{if ($item.selected) && ($period==1)}{$item.val}{else}{if $item.selected}<b>{/if}<a href='{$LINK_START}module=monitoring&ip={$ip}&year={$item.val}&period=1'>{$item.val}</a>{if $item.selected}</b>{/if}{/if} {/foreach}<br>
<br>
{if $period==0}
Пропуск: <a href='{$LINK_START}module=monitoring&ip={$ip}&period={$period}&skip={$skip+1}'>&laquo;</a> {$skip} {if ($skip!=0)}<a href='{$LINK_START}module=monitoring&ip={$ip}&period={$period}&skip={$skip-1}'>&raquo;</a>{/if}<br><br>
{/if}
{if $period>=1}Месяц: {foreach from=$months item=item}{if ($item.selected) && ($period==2)}{$item.val}{else}{if $item.selected}<b>{/if}<a href='{$LINK_START}module=monitoring&ip={$ip}&year={$year}&month={$item.val}&period=2'>{$item.val}</a>{if $item.selected}</b>{/if}{/if} {/foreach}<br>{/if}
{if $period>=2}День: {foreach from=$days item=item}{if ($item.selected) && ($period==4)}{$item.val}{else}{if $item.selected}<b>{/if}<a href='{$LINK_START}module=monitoring&ip={$ip}&year={$year}&month={$month}&day={$item.val}&period=4'>{$item.val}</a>{if $item.selected}</b>{/if}{/if} {/foreach}<br>{/if}
