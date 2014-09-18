<H2>Статистика счетов</H2>

<FORM action="?" method=get name="bill_region">
	<input type=hidden name=module value=monitoring>
	<input type=hidden name=action value=report_bill_graph>
	<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
		<TBODY>
			<TR>
				<TD class=left>
					<label for="region">Регион:</label>
				</TD>
				<TD>
					<select name="region" id="region" onchange='document.forms["bill_region"].submit();'>
						<option value="99" {if $region == 99}selected="selected"{/if}>
							Москва
						</option>
						<option value="0" {if $region == 0}selected="selected"{/if}>
								Все регионы
							</option>
						{foreach from=$regions item="r"}
							<option value="{$r.id}" {if $region == $r.id}selected="selected"{/if}>
								{$r.name}
							</option>
						{/foreach}
					</select>
				</TD>
				
			</TR>
		</TBODY>
       </TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV>
</FORM>
{if $graphs}
	<table>
	{foreach from=$graphs item="g" key="r" name="r"}
		<tr>
			<td {if $region == 0}colspan=2{/if}>
				<h2>{if $region == 99}Москва{else}{$regions.$r.name}{/if}</h2>
			</td>
		</tr>
		<tr>
			<td style="vertical-align: middle;">
				<img src="{$g.bill_totals}" {if $region == 0}width=460px height=480px{/if}>
			</td>
			{if $region != 0}
				</tr>
				<tr>
			{/if}
			<td>
				<img src="{$g.bill_details}">
			</td>
		</tr>
	{/foreach}
	</table>
{/if}





	