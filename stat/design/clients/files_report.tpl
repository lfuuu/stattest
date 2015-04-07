<H2>Отчёт по файлам</H2>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD width="3%" class=header vAlign=bottom>&#8470;</TD>
          <TD width="7%" class=header vAlign=bottom>Клиент</TD>
          <TD width="15%" class=header vAlign=bottom>Компания</TD>
          <TD width="23%" class=header vAlign=bottom>Файл</TD>
          <TD width="23%" class=header vAlign=bottom>Комментарий</TD>
          <TD width="7%" class=header vAlign=bottom>Кто</TD>
          <TD width="15%" class=header vAlign=bottom>Когда</TD>
          <TD width="7%" class=header vAlign=bottom>Менеджер</TD>
        </TR>

{foreach from=$files item=item name=outer}
    <TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<td style='font-size:85%'>{$item.no}</td>
	<td><a href='{$LINK_START}module=clients&id={$item.client_id}'>{$item.client_client}</a></td>
	<td style='font-size:85%'>{$item.client_company}</td>
	<td>
            <a href='{$LINK_START}module=clients&id={$item.id}&action=file_get&cid={$item.client_id}' {if $item.name|strlen >= 25}title="{$item.name}"{/if}>
                {if $item.name|strlen < 25}
                    {$item.name}
                {else}
                    {$item.name|truncate:25:"...":true}
                {/if}
            </a>
        </td>
	<td style='font-size:85%'>{$item.comment}</td>
	<td>{$item.user}</td>
	<td>{$item.ts|udate:"d месяца Y H:i:s"}</td>
	<td>{$item.client_manager}</td>
    </tr>
{/foreach}
</tbody></table>



      <H3>Создайте отчёт сами: (или - посмотрите отчёты за <a href="?module=clients&action=files_report&date_from={$prev_date_from}&date_to={$prev_date_to}">прошлый месяц</a>,
      								за <a href="?module=clients&action=files_report&date_from={$cur_date_from}&date_to={$cur_date_to}">текущий месяц</a>)</H3>
      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
            <FORM action="?" method=get>
            <input type=hidden name=module value=clients>
            <input type=hidden name=action value=files_report>
        <TR>
            <TD class=left>С:</TD>
            <td>
                <input class="datepicker-input" type=text class="" name="date_from" value="{$date_from}" id="date_from">
                По:<input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
            </td>
        </TR>

        <TR>
			<TD class=left>Менеджер</TD>
        	<TD><SELECT name=manager>
				<OPTION value=''{if $manager==''} selected{/if}>все</OPTION>
				{foreach from=$users item=item key=user}<option value='{$item.user}'{if $item.user==$manager} selected{/if}>{$item.name} ({$item.user})</option>{/foreach}
			</SELECT></TD>
		</TR>

        </TBODY></TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>
<script>
        optools.DatePickerInit();
</script>