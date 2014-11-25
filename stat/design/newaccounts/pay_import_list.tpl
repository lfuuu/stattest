<h2>Импорт платежей</h2>
<form action="?" method="get">
 <input type="hidden" name="module" value="newaccounts" />
 <input type="hidden" name="action" value="pi_list" />
 Показать за           <SELECT name=filter[d]>
{assign var="selected" value=''}
{if isset($filter.d)}
    {assign var="selected" value=$filter.d}
{/if}
 <option value=''{if $selected==''} selected{/if}>(любой день месяца)</OPTION>
			<OPTION value='01'{if $selected==1} selected{/if}>01</OPTION>
			<OPTION value='02'{if $selected==2} selected{/if}>02</OPTION>
			<OPTION value='03'{if $selected==3} selected{/if}>03</OPTION>
			<OPTION value='04'{if $selected==4} selected{/if}>04</OPTION>
			<OPTION value='05'{if $selected==5} selected{/if}>05</OPTION>
			<OPTION value='06'{if $selected==6} selected{/if}>06</OPTION>
			<OPTION value='07'{if $selected==7} selected{/if}>07</OPTION>
			<OPTION value='08'{if $selected==8} selected{/if}>08</OPTION>
			<OPTION value='09'{if $selected==9} selected{/if}>09</OPTION>
			<OPTION value=10{if $selected==10} selected{/if}>10</OPTION>
			<OPTION value=11{if $selected==11} selected{/if}>11</OPTION>
			<OPTION value=12{if $selected==12} selected{/if}>12</OPTION>
			<OPTION value=13{if $selected==13} selected{/if}>13</OPTION>
			<OPTION value=14{if $selected==14} selected{/if}>14</OPTION>
			<OPTION value=15{if $selected==15} selected{/if}>15</OPTION>
			<OPTION value=16{if $selected==16} selected{/if}>16</OPTION>
			<OPTION value=17{if $selected==17} selected{/if}>17</OPTION>
			<OPTION value=18{if $selected==18} selected{/if}>18</OPTION>
			<OPTION value=19{if $selected==19} selected{/if}>19</OPTION>
			<OPTION value=20{if $selected==20} selected{/if}>20</OPTION>
			<OPTION value=21{if $selected==21} selected{/if}>21</OPTION>
			<OPTION value=22{if $selected==22} selected{/if}>22</OPTION>
			<OPTION value=23{if $selected==23} selected{/if}>23</OPTION>
			<OPTION value=24{if $selected==24} selected{/if}>24</OPTION>
			<OPTION value=25{if $selected==25} selected{/if}>25</OPTION>
			<OPTION value=26{if $selected==26} selected{/if}>26</OPTION>
			<OPTION value=27{if $selected==27} selected{/if}>27</OPTION>
			<OPTION value=28{if $selected==28} selected{/if}>28</OPTION>
			<OPTION value=29{if $selected==29} selected{/if}>29</OPTION>
			<OPTION value=30{if $selected==30} selected{/if}>30</OPTION>
			<OPTION value=31{if $selected==31} selected{/if}>31</OPTION>
		</SELECT>
                {assign var="selected" value=''}
                {if isset($filter.m)}
                    {assign var="selected" value=$filter.m}
                {/if}
		<SELECT name=filter[m]>
<option value=''{if $filter.m==''} selected{/if}>(любой месяц)</OPTION>
			<OPTION value='01'{if $selected==1} selected{/if}>янв</OPTION>
			<OPTION value='02'{if $selected==2} selected{/if}>фев</OPTION>
			<OPTION value='03'{if $selected==3} selected{/if}>мар</OPTION>
			<OPTION value='04'{if $selected==4} selected{/if}>апр</OPTION>
			<OPTION value='05'{if $selected==5} selected{/if}>мая</OPTION>
			<OPTION value='06'{if $selected==6} selected{/if}>июн</OPTION>
			<OPTION value='07'{if $selected==7} selected{/if}>июл</OPTION>
			<OPTION value='08'{if $selected==8} selected{/if}>авг</OPTION>
			<OPTION value='09'{if $selected==9} selected{/if}>сен</OPTION>
			<OPTION value=10{if $selected==10} selected{/if}>окт</OPTION>
			<OPTION value=11{if $selected==11} selected{/if}>ноя</OPTION>
			<OPTION value=12{if $selected==12} selected{/if}>дек</OPTION>
		</SELECT>
                {assign var="selected" value=''}
                {if isset($filter.y)}
                    {assign var="selected" value=$filter.y}
                {/if}
		<SELECT name=filter[y]>
<option value=''{if $selected==''} selected{/if}>(любой год)</OPTION>
			{generate_sequence_options_select start=2003 selected=$selected}
		</SELECT><input type="submit" class=button value="Показать" />
</form><br>

<table style="text-align: center;border-collapse: collapse;" border=1 colspan=1 cellspacing=0 width=60%>
<tr>
<td>/</td>
{foreach from=$l1 item=c key=k}
<td colspan={$c.colspan} style="background-color: {if $k=="mcn"}#f5e1e1{elseif $k == "all4net"}#fbfbdd{else}#f0fff0{/if};"><b>{$c.title}</b></td>
{/foreach}
</tr>
{foreach from=$payments key=date item=di}
<tr>
<td><b>{$date|mdate:"d-m-Y"}</b></td>
{foreach from=$companyes key=k item=i}
{foreach from=$i.acc item=a}
<td style="padding: 3px 3px 3px 3px;background-color: {if $k=="mcn"}#f5e1e1{elseif $k == "all4net"}#fbfbdd{else}#f0fff0{/if};">
    {if isset($di[$k][$a]) && $di[$k][$a]}
        <a href=".?module=newaccounts&action=pi_process&file={$di[$k][$a]}">{$a}</a>
    {else}
        &nbsp;
    {/if}
    {if $a == "citi" && isset($di[$k].citi_info) && $di[$k].citi_info}
        <sup title="Дополнительная информация к платежам загруженна" style="color:#20a420; font-size: 7pt;">+info</sup>
    {/if}
</td>
{/foreach}
{/foreach}
</tr>
{/foreach}
</table>

{*foreach from=$payments item=file name=outer}
<a href='?module=newaccounts&action=pi_process&file={$file}'>{$file}</a><br>
{/foreach*}
<br><br>
<form enctype="multipart/form-data" action="?" method="post">
 <input type="hidden" name="module" value="newaccounts" />
 <input type="hidden" name="action" value="pi_upload" />
 Выберите файл с платежами: <input class=text name="file" type="file" />
 <input type="submit" class=button value="Загрузить" />
</form>
