      <H2>Статистика</H2>
      <H3>VoIP</H3>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom>Дата/время</TD>
{if $detality=='call'}
          <TD class=header vAlign=bottom>Кто звонил</TD>
          <TD class=header vAlign=bottom>Кому звонил</TD>
		  <td class="header" valign="bottom">Себестоимость</td>
		  <td class="header" valign="bottom">Оператор</td>
{else}
          <TD class=header vAlign=bottom>Число звонков</TD>
{/if}
          <TD class=header vAlign=bottom>Время разговора</TD>
          <TD class=header vAlign=bottom>Стоимость разговора</TD>
        </TR>

{foreach from=$stats item=item key=key name=outer}
	<TR class='{cycle values="even,odd"}'>
		<TD>{$item.date}</TD>
{if $detality=='call'}
		<TD>{$item.caller}</TD>
		<TD>{$item.called}</TD>
		<td>{$item.price}</td>
		<td>{$item.operator}</td>
{else}		
		<TD>{$item.cnt}</TD>
{/if}
		<TD><b>{$item.hulen}</b></TD>
		<TD>{$item.sum}</TD>
	</TR>
{/foreach}
</TBODY></TABLE>
