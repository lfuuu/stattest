      <H2>Статистика</H2>
      <H3>VoIP</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
{if $detality=='call'}
          <TD class=header vAlign=bottom>Id</TD>
          <TD class=header vAlign=bottom>Дата/время</TD>
          {if $phone=='all_regions'}<TD class=header vAlign=bottom>Регион</TD>{/if}
          <TD class=header vAlign=bottom>Исходящий номер</TD>
          <TD class=header vAlign=bottom>Направление</TD>
          <TD class=header vAlign=bottom>Входящий номер</TD>
          <TD class=header vAlign=bottom>Время разговора</TD>
          <TD class=header vAlign=bottom>Стоимость (без НДС)</TD>
          <TD class=header vAlign=bottom>Назначение</TD>
{else}
          <TD class=header vAlign=bottom>Дата/время</TD>
          <TD class=header vAlign=bottom>Число звонков</TD>
          <TD class=header vAlign=bottom>Время разговора</TD>
          <TD class=header vAlign=bottom>Стоимость (без НДС)</TD>
{/if}
        </TR>

{foreach from=$stats item=item key=key name=outer}
    <TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
{if $detality=='call'}
    <TD style="color:gray">{if isset($item.id)}{$item.id}{/if}</TD>
    <TD>{$item.tsf1}</TD>
    {if $phone=='all_regions'}<TD>{$item.reg_id}</TD>{/if}
    <TD>{if isset($item.src_number)}{$item.src_number}{/if}</TD>
    <TD style="color: {if isset($item.orig) && $item.orig=='f'}blue;">&darr;&nbsp;входящий{elseif isset($item.orig) && $item.orig=='t'}green">&uarr;&nbsp;исходящий{else}">{/if}</td>
    <TD>{if isset($item.dst_number)}{$item.dst_number}{/if}</TD>
    <TD><b>{$item.tsf2}</b></TD>
    {if $smarty.foreach.outer.last}
         <TD colspan='2'>{$item.price}</TD>
    {else}
        <TD>{$item.price}</TD>
        <TD>{$item.geo}</TD>
    {/if}    
{else}
    <TD>{$item.tsf1}</TD>
    <TD>{$item.cnt}</TD>
    <TD><b>{$item.tsf2}</b></TD>
    <TD>{$item.price}</TD>
{/if}
    </TR>
{/foreach}
</TBODY></TABLE>
