      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
            <FORM action="?" method=get>
            <input type=hidden name=module value=stats>
            <input type=hidden name=action value=report>
        <TR>
          <TD class=left>Отчёт за дату</TD>
          <TD>
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
			<option value=0{if $d==0} selected{/if}>Весь месяц</option>
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
			{generate_sequence_options_select start=2005 selected=$y}
		</SELECT> </TD></TR>
        <TR>
            <TD class=left><input type=checkbox name="is_in_less_out" value="true"{if $is_in_less_out} checked{/if}> Превыщение исходящего над входящим <span style="background-color:red;  display:inline; padding: 6px;">&nbsp;</span></TD>
            
            <TD></td>
		</TR>
        <TR>
            <TD class=left><input type=checkbox name="is_over" value="true"{if $is_over} checked{/if}> Превышение, раз <span style="background-color:blue; display:inline; padding: 6px;">&nbsp;</span></TD>
            <TD><input class=text name=over value="{$over}"></td>
		</TR>
        <TR>
            <TD class=left><input type=checkbox name="is_traf_less" value="true"{if $is_traf_less} checked{/if}> Суммарный трафик меньше: <span style="background-color:magenta;  display:inline; padding: 6px;">&nbsp;</span></TD>
            <TD><input class=text name=traf_less value="{$traf_less}"> Мб</td>
		</TR>
		<TR>
            <TD class=left><input type=checkbox name="show_unlim" value="true"{if $show_unlim} checked{/if}> Включать безлимитные тарифы</TD>
            <TD>&nbsp;</td>
		</TR>
		<TR>
            <TD class=left><input type=checkbox name="show_tarif_traf" value="true"{if $show_tarif_traf} checked{/if}> Отображать трафик тарифа</TD>
            <TD>&nbsp;</td>
		</TR>
        </TBODY></TABLE>
      <HR>

<DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>
