<H2>Статистика счетов</H2>

<FORM action="?" method=get>
	<input type=hidden name=module value=monitoring>
	<input type=hidden name=action value=report_bill_graph>
	<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
		<TBODY>
			<TR>
				<TD class=left>
					<label for="region">Регион:</label>
				</TD>
				<TD>
					<select name="region" id="region">
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
	{if $region == 99}
		<h2>Москва</h2>
	{/if}
	{foreach from=$graphs item="g" key="r" name="r"}
		<h2>{$regions.$r.name}</h2>
		<div>
			<img src="{$g.bill_totals}">
		</div>
		<div>
			<img src="{$g.bill_details}">
		</div>
		{if !$smarty.foreach.r.last}
			<hr/>
		{/if}
	{/foreach}
{/if}





	