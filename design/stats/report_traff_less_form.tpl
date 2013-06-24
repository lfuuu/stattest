      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
            <FORM action="?" method=get>
            <input type=hidden name=module value=stats>
            <input type=hidden name=action value=report_traff_less>
        <TR>
          <TD class=left>Отчёт за период</TD>
          <TD>

           с:
          <SELECT name=d>
			<OPTION value=1{if $d==1} selected{/if}>01</OPTION>
			<OPTION value=2{if $d==2} selected{/if}>02</OPTION>
			<OPTION value=3{if $d==3} selected{/if}>03</OPTION>
			<OPTION value=4{if $d==4} selected{/if}>04</OPTION>
			<OPTION value=5{if $d==5} selected{/if}>05</OPTION>
			<OPTION value=6{if $d==6} selected{/if}>06</OPTION>
			<OPTION value=7{if $d==7} selected{/if}>07</OPTION>
			<OPTION value=8{if $d==8} selected{/if}>08</OPTION>
			<OPTION value=9{if $d==9} selected{/if}>09</OPTION>
			<OPTION value=10{if $d==10} selected{/if}>10</OPTION>
			<OPTION value=11{if $d==11} selected{/if}>11</OPTION>
			<OPTION value=12{if $d==12} selected{/if}>12</OPTION>
			<OPTION value=13{if $d==13} selected{/if}>13</OPTION>
			<OPTION value=14{if $d==14} selected{/if}>14</OPTION>
			<OPTION value=15{if $d==15} selected{/if}>15</OPTION>
			<OPTION value=16{if $d==16} selected{/if}>16</OPTION>
			<OPTION value=17{if $d==17} selected{/if}>17</OPTION>
			<OPTION value=18{if $d==18} selected{/if}>18</OPTION>
			<OPTION value=19{if $d==19} selected{/if}>19</OPTION>
			<OPTION value=20{if $d==20} selected{/if}>20</OPTION>
			<OPTION value=21{if $d==21} selected{/if}>21</OPTION>
			<OPTION value=22{if $d==22} selected{/if}>22</OPTION>
			<OPTION value=23{if $d==23} selected{/if}>23</OPTION>
			<OPTION value=24{if $d==24} selected{/if}>24</OPTION>
			<OPTION value=25{if $d==25} selected{/if}>25</OPTION>
			<OPTION value=26{if $d==26} selected{/if}>26</OPTION>
			<OPTION value=27{if $d==27} selected{/if}>27</OPTION>
			<OPTION value=28{if $d==28} selected{/if}>28</OPTION>
			<OPTION value=29{if $d==29} selected{/if}>29</OPTION>
			<OPTION value=30{if $d==30} selected{/if}>30</OPTION>
			<OPTION value=31{if $d==31} selected{/if}>31</OPTION>
		</SELECT>          
		<SELECT name=m>
			<OPTION value=1{if $m==1} selected{/if}>янв</OPTION>
			<OPTION value=2{if $m==2} selected{/if}>фев</OPTION>
			<OPTION value=3{if $m==3} selected{/if}>мар</OPTION>
			<OPTION value=4{if $m==4} selected{/if}>апр</OPTION>
			<OPTION value=5{if $m==5} selected{/if}>мая</OPTION>
			<OPTION value=6{if $m==6} selected{/if}>июн</OPTION>
			<OPTION value=7{if $m==7} selected{/if}>июл</OPTION>
			<OPTION value=8{if $m==8} selected{/if}>авг</OPTION>
			<OPTION value=9{if $m==9} selected{/if}>сен</OPTION>
			<OPTION value=10{if $m==10} selected{/if}>окт</OPTION>
			<OPTION value=11{if $m==11} selected{/if}>ноя</OPTION>
			<OPTION value=12{if $m==12} selected{/if}>дек</OPTION>
		</SELECT>
		<SELECT name=y>
			{generate_sequence_options_select start=2003 selected=$y}
		</SELECT> 
        по:
          <SELECT name=ed>
			<OPTION value=1{if $ed==1} selected{/if}>01</OPTION>
			<OPTION value=2{if $ed==2} selected{/if}>02</OPTION>
			<OPTION value=3{if $ed==3} selected{/if}>03</OPTION>
			<OPTION value=4{if $ed==4} selected{/if}>04</OPTION>
			<OPTION value=5{if $ed==5} selected{/if}>05</OPTION>
			<OPTION value=6{if $ed==6} selected{/if}>06</OPTION>
			<OPTION value=7{if $ed==7} selected{/if}>07</OPTION>
			<OPTION value=8{if $ed==8} selected{/if}>08</OPTION>
			<OPTION value=9{if $ed==9} selected{/if}>09</OPTION>
			<OPTION value=10{if $ed==10} selected{/if}>10</OPTION>
			<OPTION value=11{if $ed==11} selected{/if}>11</OPTION>
			<OPTION value=12{if $ed==12} selected{/if}>12</OPTION>
			<OPTION value=13{if $ed==13} selected{/if}>13</OPTION>
			<OPTION value=14{if $ed==14} selected{/if}>14</OPTION>
			<OPTION value=15{if $ed==15} selected{/if}>15</OPTION>
			<OPTION value=16{if $ed==16} selected{/if}>16</OPTION>
			<OPTION value=17{if $ed==17} selected{/if}>17</OPTION>
			<OPTION value=18{if $ed==18} selected{/if}>18</OPTION>
			<OPTION value=19{if $ed==19} selected{/if}>19</OPTION>
			<OPTION value=20{if $ed==20} selected{/if}>20</OPTION>
			<OPTION value=21{if $ed==21} selected{/if}>21</OPTION>
			<OPTION value=22{if $ed==22} selected{/if}>22</OPTION>
			<OPTION value=23{if $ed==23} selected{/if}>23</OPTION>
			<OPTION value=24{if $ed==24} selected{/if}>24</OPTION>
			<OPTION value=25{if $ed==25} selected{/if}>25</OPTION>
			<OPTION value=26{if $ed==26} selected{/if}>26</OPTION>
			<OPTION value=27{if $ed==27} selected{/if}>27</OPTION>
			<OPTION value=28{if $ed==28} selected{/if}>28</OPTION>
			<OPTION value=29{if $ed==29} selected{/if}>29</OPTION>
			<OPTION value=30{if $ed==30} selected{/if}>30</OPTION>
			<OPTION value=31{if $ed==31} selected{/if}>31</OPTION>
		</SELECT>          
		<SELECT name=em>
			<OPTION value=1{if $em==1} selected{/if}>янв</OPTION>
			<OPTION value=2{if $em==2} selected{/if}>фев</OPTION>
			<OPTION value=3{if $em==3} selected{/if}>мар</OPTION>
			<OPTION value=4{if $em==4} selected{/if}>апр</OPTION>
			<OPTION value=5{if $em==5} selected{/if}>мая</OPTION>
			<OPTION value=6{if $em==6} selected{/if}>июн</OPTION>
			<OPTION value=7{if $em==7} selected{/if}>июл</OPTION>
			<OPTION value=8{if $em==8} selected{/if}>авг</OPTION>
			<OPTION value=9{if $em==9} selected{/if}>сен</OPTION>
			<OPTION value=10{if $em==10} selected{/if}>окт</OPTION>
			<OPTION value=11{if $em==11} selected{/if}>ноя</OPTION>
			<OPTION value=12{if $em==12} selected{/if}>дек</OPTION>
		</SELECT>
		<SELECT name=ey>
			{generate_sequence_options_select start=2003 selected=$ey}
		</SELECT> 
        </TD></TR>
        <TR>
            <TD class=left>Суммарный трафик меньше: </TD>
            <TD><input class=text name=traf_less value="{$traf_less}"> Мб/день</td>
		</TR>
		<TR>
            <TD class=left>Менеджер: </TD>
            <TD>
				<select name='manager'>
					{html_options options=$managers selected=$manager}
				</select>
			</TD>
		</TR>
		<tr><td class=left>Включать клиентов, отключенных в отчетный период:</td><td><input type='checkbox' {if $offclients}checked='checked'{/if} name='offclients' /></td></tr>
        </TBODY></TABLE>
      <HR>

<DIV align=center><INPUT class=button type=submit name=make_report value="Сформировать отчёт"></DIV></FORM>
