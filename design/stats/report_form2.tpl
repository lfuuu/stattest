      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
            <FORM action="?" method=get>
            <input type=hidden name=module value=stats>
            <input type=hidden name=action value=reportready>
        <TR>
          <TD class=left>Отчёт за месяц</TD>
          <TD>
		<SELECT name=month>
			<OPTION value=1{if $month==1} selected{/if}>янв</OPTION>
			<OPTION value=2{if $month==2} selected{/if}>фев</OPTION>
			<OPTION value=3{if $month==3} selected{/if}>мар</OPTION>
			<OPTION value=4{if $month==4} selected{/if}>апр</OPTION>
			<OPTION value=5{if $month==5} selected{/if}>мая</OPTION>
			<OPTION value=6{if $month==6} selected{/if}>июн</OPTION>
			<OPTION value=7{if $month==7} selected{/if}>июл</OPTION>
			<OPTION value=8{if $month==8} selected{/if}>авг</OPTION>
			<OPTION value=9{if $month==9} selected{/if}>сен</OPTION>
			<OPTION value=10{if $month==10} selected{/if}>окт</OPTION>
			<OPTION value=11{if $month==11} selected{/if}>ноя</OPTION>
			<OPTION value=12{if $month==12} selected{/if}>дек</OPTION>
		</SELECT>
		<SELECT name=year>
			{generate_sequence_options_select start=2003 selected=$year}
		</SELECT> </TD></TR><TR>
        <TR>
          <TD class=left>Менеджер</TD>
          <TD>
		<SELECT name=manager>
			<OPTION value=''{if $manager==''} selected{/if}>все</OPTION>
{foreach from=$users item=item key=user}<option value='{$item.user}'{if $item.user==$manager} selected{/if}>{$item.name} ({$item.user})</option>{/foreach}</SELECT>
			</TD></TR>
        </TBODY></TABLE>
      <HR>

<DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>