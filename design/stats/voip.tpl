      <H2>Статистика</H2>
      <H3>VoIP</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
{if $detality=='call'}
          <TD class=header vAlign=bottom>Id</TD>
          <TD class=header vAlign=bottom>Дата/время</TD>
          {if $phone=='all_regions'}<TD>Регион</TD>{/if}
          <TD class=header vAlign=bottom>Номер абонента</TD>
          <TD class=header vAlign=bottom>Направление</TD>
          <TD class=header vAlign=bottom>Внешний номер</TD>
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
    <TD style="color:gray">{$item.id}</TD>
    <TD>{$item.tsf1}</TD>
	{if $phone=='all_regions'}<TD>{$item.reg_id}</TD>{/if}
	<TD>{$item.usage_num}</TD>
    <TD style="color: {if $item.direction_out=='f'}blue;">&darr;&nbsp;входящий{elseif $item.direction_out=='t'}green">&uarr;&nbsp;исходящий{else}">{/if}</td>
	<TD>{$item.phone_num}</TD>
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
