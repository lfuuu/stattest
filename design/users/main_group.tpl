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
<H3>Права доступа</H3>
      <TABLE cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
{foreach from=$rights item=supitem key=supkey name=outer}
		<TR><TD colspan=2><h3>{$supkey}</h3></TD></TR>
{foreach from=$supitem item=item key=right name=fe}
          <TR><TD>{$item.comment} (<b>{$right}</b>)<br>{foreach from=$item.values item=item2 key=key2 name=inner}{if $key2>0}; {/if}<b>{$item2}</b> - {$item.values_desc[$key2]}{/foreach}</TD><TD width=40%>
          <input name=rights[{$right}] class=text value='{$rights_group.$right}' style='width:100%'>
          </TD></TR>
{/foreach}
{/foreach}
          </TBODY></TABLE>
	<HR>
{/if}

{if access('users','change')}
<DIV align=center><INPUT class=button type=submit value="Изменить"></DIV>
{/if}
</FORM>