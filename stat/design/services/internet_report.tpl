<H2>Отчёт по подключениям</H2>
      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom>&#8470;</TD>
          <TD class=header vAlign=bottom>ID</TD>
          <TD class=header vAlign=bottom>Клиент</TD>
          <TD class=header vAlign=bottom>Менеджер</TD>
          <TD class=header vAlign=bottom>Тариф</TD>
          <TD class=header vAlign=bottom>Скорость ADSL</TD>
          <TD class=header vAlign=bottom>Подключение</TD>
          <td class=header vAlign=bottom>Активно</TD>
        </TR>

{foreach from=$ports item=item}<tr>
	<td>{$item.ord}</td>
	<td><a href='{$PATH_TO_ROOT}pop_services.php?table=usage_ip_ports&id={$item.id}'>{$item.id}</a></td>
	<td><a href='/client/view?id={$item.clientid}'>{$item.client}</a></td>
	<td>{$item.manager}</td>
	<td>{if isset($item.tarif.name)}
		<span title='Текущий тариф: {$item.tarif.mb_month}-{$item.tarif.pay_month}-{$item.tarif.pay_mb}'>{$item.tarif.name}</span>
		<span style='font-size:85%'>{$item.tarif.comment}</span>
		{else}не установлен{/if}
	</td>
    <td>{if isset($item.tarif.name)}{if $item.tarif.adsl_speed == $item.speed_mgts}{$item.speed_mgts}{else}<b style="color: {if $item.tarif.adsl_speed != "768/6144"}#c40000{else}#c4c400{/if};">{$item.speed_mgts} ({$item.tarif.adsl_speed})</b>{/if}{/if}</td>
	<td><nobr><b>{$item.port_type}, {if $item.port=='mgts'}{$item.node}{else}<a href='{$LINK_START}module=routers&id={$item.node}'>{$item.node}</a>::{$item.port}{/if}</b></nobr></td>
	<TD>{$item.actual_from}/{if !$item.actual}{$item.actual_to}{/if}</td>
</tr>{/foreach}
</tbody></table>



      <H3>Создайте отчёт сами: (или - посмотрите отчёты за <a href="?module=services&action=in_report&port_type={$port_type}&from_d={$prev_from_d}&to_d={$prev_to_d}&from_m={$prev_from_m}&to_m={$prev_to_m}&from_y={$prev_from_y}&to_y={$prev_to_y}">прошлый месяц</a>,
      								за <a href="?module=services&action=in_report&port_type={$port_type}&from_d={$cur_from_d}&to_d={$cur_to_d}&from_m={$cur_from_m}&to_m={$cur_to_m}&from_y={$cur_from_y}&to_y={$cur_to_y}">текущий месяц</a>)</H3>
      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
            <FORM action="?" method=get>
            <input type=hidden name=module value=services>
            <input type=hidden name=action value=in_report>
        <TR>
          <TD class=left>Дата начала отчёта</TD>
          <TD>
          <SELECT name=from_d>
			<OPTION value=1{if $from_d==1} selected{/if}>01</OPTION>
			<OPTION value=2{if $from_d==2} selected{/if}>02</OPTION>
			<OPTION value=3{if $from_d==3} selected{/if}>03</OPTION>
			<OPTION value=4{if $from_d==4} selected{/if}>04</OPTION>
			<OPTION value=5{if $from_d==5} selected{/if}>05</OPTION>
			<OPTION value=6{if $from_d==6} selected{/if}>06</OPTION>
			<OPTION value=7{if $from_d==7} selected{/if}>07</OPTION>
			<OPTION value=8{if $from_d==8} selected{/if}>08</OPTION>
			<OPTION value=9{if $from_d==9} selected{/if}>09</OPTION>
			<OPTION value=10{if $from_d==10} selected{/if}>10</OPTION>
			<OPTION value=11{if $from_d==11} selected{/if}>11</OPTION>
			<OPTION value=12{if $from_d==12} selected{/if}>12</OPTION>
			<OPTION value=13{if $from_d==13} selected{/if}>13</OPTION>
			<OPTION value=14{if $from_d==14} selected{/if}>14</OPTION>
			<OPTION value=15{if $from_d==15} selected{/if}>15</OPTION>
			<OPTION value=16{if $from_d==16} selected{/if}>16</OPTION>
			<OPTION value=17{if $from_d==17} selected{/if}>17</OPTION>
			<OPTION value=18{if $from_d==18} selected{/if}>18</OPTION>
			<OPTION value=19{if $from_d==19} selected{/if}>19</OPTION>
			<OPTION value=20{if $from_d==20} selected{/if}>20</OPTION>
			<OPTION value=21{if $from_d==21} selected{/if}>21</OPTION>
			<OPTION value=22{if $from_d==22} selected{/if}>22</OPTION>
			<OPTION value=23{if $from_d==23} selected{/if}>23</OPTION>
			<OPTION value=24{if $from_d==24} selected{/if}>24</OPTION>
			<OPTION value=25{if $from_d==25} selected{/if}>25</OPTION>
			<OPTION value=26{if $from_d==26} selected{/if}>26</OPTION>
			<OPTION value=27{if $from_d==27} selected{/if}>27</OPTION>
			<OPTION value=28{if $from_d==28} selected{/if}>28</OPTION>
			<OPTION value=29{if $from_d==29} selected{/if}>29</OPTION>
			<OPTION value=30{if $from_d==30} selected{/if}>30</OPTION>
			<OPTION value=31{if $from_d==31} selected{/if}>31</OPTION>
		</SELECT>
		<SELECT name=from_m>
			<OPTION value=1{if $from_m==1} selected{/if}>янв</OPTION>
			<OPTION value=2{if $from_m==2} selected{/if}>фев</OPTION>
			<OPTION value=3{if $from_m==3} selected{/if}>мар</OPTION>
			<OPTION value=4{if $from_m==4} selected{/if}>апр</OPTION>
			<OPTION value=5{if $from_m==5} selected{/if}>мая</OPTION>
			<OPTION value=6{if $from_m==6} selected{/if}>июн</OPTION>
			<OPTION value=7{if $from_m==7} selected{/if}>июл</OPTION>
			<OPTION value=8{if $from_m==8} selected{/if}>авг</OPTION>
			<OPTION value=9{if $from_m==9} selected{/if}>сен</OPTION>
			<OPTION value=10{if $from_m==10} selected{/if}>окт</OPTION>
			<OPTION value=11{if $from_m==11} selected{/if}>ноя</OPTION>
			<OPTION value=12{if $from_m==12} selected{/if}>дек</OPTION>
		</SELECT>
		<SELECT name=from_y>
			{generate_sequence_options_select start='2003' selected=$from_y}
		</SELECT> </TD></TR>
        <TR>
          <TD class=left>По какую дату</TD>
          <TD>
		<SELECT name=to_d>
			<OPTION value=1{if $to_d==1} selected{/if}>01</OPTION>
			<OPTION value=2{if $to_d==2} selected{/if}>02</OPTION>
			<OPTION value=3{if $to_d==3} selected{/if}>03</OPTION>
			<OPTION value=4{if $to_d==4} selected{/if}>04</OPTION>
			<OPTION value=5{if $to_d==5} selected{/if}>05</OPTION>
			<OPTION value=6{if $to_d==6} selected{/if}>06</OPTION>
			<OPTION value=7{if $to_d==7} selected{/if}>07</OPTION>
			<OPTION value=8{if $to_d==8} selected{/if}>08</OPTION>
			<OPTION value=9{if $to_d==9} selected{/if}>09</OPTION>
			<OPTION value=10{if $to_d==10} selected{/if}>10</OPTION>
			<OPTION value=11{if $to_d==11} selected{/if}>11</OPTION>
			<OPTION value=12{if $to_d==12} selected{/if}>12</OPTION>
			<OPTION value=13{if $to_d==13} selected{/if}>13</OPTION>
			<OPTION value=14{if $to_d==14} selected{/if}>14</OPTION>
			<OPTION value=15{if $to_d==15} selected{/if}>15</OPTION>
			<OPTION value=16{if $to_d==16} selected{/if}>16</OPTION>
			<OPTION value=17{if $to_d==17} selected{/if}>17</OPTION>
			<OPTION value=18{if $to_d==18} selected{/if}>18</OPTION>
			<OPTION value=19{if $to_d==19} selected{/if}>19</OPTION>
			<OPTION value=20{if $to_d==20} selected{/if}>20</OPTION>
			<OPTION value=21{if $to_d==21} selected{/if}>21</OPTION>
			<OPTION value=22{if $to_d==22} selected{/if}>22</OPTION>
			<OPTION value=23{if $to_d==23} selected{/if}>23</OPTION>
			<OPTION value=24{if $to_d==24} selected{/if}>24</OPTION>
			<OPTION value=25{if $to_d==25} selected{/if}>25</OPTION>
			<OPTION value=26{if $to_d==26} selected{/if}>26</OPTION>
			<OPTION value=27{if $to_d==27} selected{/if}>27</OPTION>
			<OPTION value=28{if $to_d==28} selected{/if}>28</OPTION>
			<OPTION value=29{if $to_d==29} selected{/if}>29</OPTION>
			<OPTION value=30{if $to_d==30} selected{/if}>30</OPTION>
			<OPTION value=31{if $to_d==31} selected{/if}>31</OPTION>
		</SELECT>
		<SELECT name=to_m>
			<OPTION value=1{if $to_m==1} selected{/if}>янв</OPTION>
			<OPTION value=2{if $to_m==2} selected{/if}>фев</OPTION>
			<OPTION value=3{if $to_m==3} selected{/if}>мар</OPTION>
			<OPTION value=4{if $to_m==4} selected{/if}>апр</OPTION>
			<OPTION value=5{if $to_m==5} selected{/if}>мая</OPTION>
			<OPTION value=6{if $to_m==6} selected{/if}>июн</OPTION>
			<OPTION value=7{if $to_m==7} selected{/if}>июл</OPTION>
			<OPTION value=8{if $to_m==8} selected{/if}>авг</OPTION>
			<OPTION value=9{if $to_m==9} selected{/if}>сен</OPTION>
			<OPTION value=10{if $to_m==10} selected{/if}>окт</OPTION>
			<OPTION value=11{if $to_m==11} selected{/if}>ноя</OPTION>
			<OPTION value=12{if $to_m==12} selected{/if}>дек</OPTION>
		</SELECT>
		<SELECT name=to_y>
			{generate_sequence_options_select start='2003'}
			<OPTION value=4000{if $to_y==4000} selected{/if}>4000</OPTION>
		</SELECT> </TD></TR>


        <TR>
			<TD class=left>Тип подключения</TD>
        	<TD><SELECT name='port_type[]' multiple size='10'>
				{foreach from=$port_types item=item}<option value='{$item}'{if in_array($item,$port_type)} selected{/if}>{$item}</option>{/foreach}
			</SELECT></TD>
		</TR>
        <TR>
			<TD class=left>Скрывать низкоскоростные подключения</TD>
        	<TD><input type=checkbox value=1 name="hide_slow"{if $hide_slow} checked{/if}></TD>
		</TR>
        <TR>
			<TD class=left>Скрывать отключенных в отчетный период</TD>
        	<TD><input type=checkbox value=1 name="hide_off"{if $hide_off} checked{/if}></TD>
		</TR>
        <TR>
			<TD class=left>Только безлимитные тарифы</TD>
        	<TD><input type=checkbox value=1 name="unlim"{if $unlim} checked{/if}></TD>
		</TR>
		<tr>
			<TD class=left>Выбрать отключения</TD>
        	<TD><input type=checkbox value=1 name="show_off"{if $show_off} checked{/if}></TD>
		</tr>
		<tr>
			<td class=left>Менеджер</td>
			<td><SELECT name=manager>
				<option value=''>не определено</option>
				{foreach from=$managers item=item key=user}<option value='{$item.user}'{if $item.user==$manager} selected{/if}>{$item.name} ({$item.user})</option>{/foreach}
			</select></td>
		</tr>
        </TBODY></TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>
