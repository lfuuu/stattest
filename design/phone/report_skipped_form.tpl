      <H3>Создайте отчёт сами: (или - посмотрите отчёты за <a href="?module=phone&action=report&detality=day&from_d={$prev_from_d}&to_d={$prev_to_d}&from_m={$prev_from_m}&to_m={$prev_to_m}&from_y={$prev_from_y}&to_y={$prev_to_y}">прошлый месяц</a>,
      								за <a href="?module=phone&action=report&detality=day&from_d={$cur_from_d}&to_d={$cur_to_d}&from_m={$cur_from_m}&to_m={$cur_to_m}&from_y={$cur_from_y}&to_y={$cur_to_y}">текущий месяц</a>)</H3>
      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
  
        <TBODY>
        <TR>
          <TD class=left>Телефон:</TD>
          <TD>
            <FORM action="?" method=get>
            <input type=hidden name=module value=phone>
            <input type=hidden name=action value=report>
            <SELECT name=phone>
            	<OPTION value=0{if !$phone} selected{/if}>все</OPTION>
				{foreach from=$phones item=item}<option value='{$item}'{if $phone==$item} selected{/if}>{$item}</option>{/foreach}
              </SELECT>
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
			<OPTION value=2004{if $from_y==2004} selected{/if}>2004</OPTION>
			<OPTION value=2005{if $from_y==2005} selected{/if}>2005</OPTION>
			<OPTION value=2006{if $from_y==2006} selected{/if}>2006</OPTION>
			<OPTION value=2007{if $from_y==2007} selected{/if}>2007</OPTION>
			<OPTION value=2008{if $from_y==2008} selected{/if}>2008</OPTION>
			<OPTION value=2009{if $from_y==2009} selected{/if}>2009</OPTION>
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
			<OPTION value=2003{if $to_y==2003} selected{/if}>2003</OPTION>
			<OPTION value=2004{if $to_y==2004} selected{/if}>2004</OPTION>
			<OPTION value=2005{if $to_y==2005} selected{/if}>2005</OPTION>
			<OPTION value=2006{if $to_y==2006} selected{/if}>2006</OPTION>
			<OPTION value=2007{if $to_y==2007} selected{/if}>2007</OPTION>
			<OPTION value=2008{if $to_y==2008} selected{/if}>2008</OPTION>
		</SELECT> </TD></TR><TR>
          <TD class=left>Выводить по:</TD>
          <TD>
		<SELECT name=detality>
			<OPTION value=call{if $detality=='call'} selected{/if}>звонкам</OPTION>
			<OPTION value=day{if $detality=='day'} selected{/if}>дням</OPTION>
			<OPTION value=month{if $detality=='month'} selected{/if}>месяцам</OPTION>
			<OPTION value=year{if $detality=='year'} selected{/if}>годам</OPTION>
		</SELECT>
        </TD></TR></TBODY></TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM><!-- ######## /Content ######## -->