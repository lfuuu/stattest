Дата {$date_from}<br>
<H1>РЕЕСТР</H1>
<h2>заказных почтовых отправлений</h2>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom>&#8470; п/п</TD>
          <TD class=header vAlign=bottom>Индекс</TD>
          <TD class=header vAlign=bottom>Адрес</TD>
          <TD class=header vAlign=bottom>Кому</TD>
          <TD class=header vAlign=bottom>Куда (город)</TD>
        </TR>

{foreach from=$postregs item=item}<tr>
	<td>{$item.ord}</td>
	<td>{$item._zip}</td>
	<td>{$item._addr}</td>
	<td>{$item.company}</td>
	<td>{$item._city}</td>
</tr>{/foreach}
</tbody></table>
