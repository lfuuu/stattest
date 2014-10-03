<H2>Группы</H2>
<H3>Изменение группы {$usergroup.usergroup}:</H3>
<FORM action="?" method=post>
<input type=hidden name=action value=edit>
<input type=hidden name=module value=users>
<input type=hidden name=m value=group>
<input type=hidden name=id value='{$usergroup.usergroup}'>

      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TR><TD class=left>Группа:</TD><TD>
{if access('users','change')}
          <input name=newusergroup class=text value='{$usergroup.usergroup}'>
{else}
			{$usergroup.usergroup}
{/if}
          </TD></TR>
          
          <TR><TD class=left>Комментарий:</TD><TD>
{if access('users','change')}
          <input name=comment class=text value='{$usergroup.comment}'>
{else}
			{$usergroup.comment}
{/if}
          </TD></TR>

          </TBODY></TABLE>

{if access('users','grant')}
<H2>Права доступа</H2>
      <TABLE cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
{foreach from=$rights item=supitem key=supkey name=outer}
	<TR>
		<TD colspan=2>
			<span style="font-weight: bold; font-size: 17px;">{$supkey}</span>
		</TD>
	</TR>
	{foreach from=$supitem item=item key=right name=fe}
		<TR>
			<TD valign=top>
				<span style="padding-left: 15px; font-weight: bold; font-size: 14px;">{$item.comment} ({$right})</span>
			</TD>
			<TD width=40%>
				{assign var="applied_rights" value=","|explode:$rights_group.$right}
				{foreach from=$item.values item=item2 key=key2 name=inner}
					<div>
						<input type="checkbox" id="{$right}_{$item2}"{if $item2|in_array:$applied_rights} checked{/if} value="{$item2}" name="rights[{$right}][]" >
						<label for="{$right}_{$item2}">{$item.values_desc[$key2]} (<b>{$item2}</b>)</label>
					</div>
					{*if $key2>0}; {/if}<b>{$item2}</b> - {$item.values_desc[$key2]*}
				{/foreach}
			</TD>
		</TR>
		<tr>
			<td colspan=2><hr style="background-color: #CCCCCC;color: #CCCCCC;"></td>
		</tr>
	{/foreach}
{/foreach}
          </TBODY></TABLE>
	<HR>
{/if}

{if access('users','change')}
<DIV align=center><INPUT class=button type=submit value="Изменить"></DIV>
{/if}
</FORM>