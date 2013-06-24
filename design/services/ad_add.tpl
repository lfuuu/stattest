<H2>Услуги</H2>
<H3>Доп. услуги лучше заводить <a href='{$LINK_START}module=services&action=ex_add'>нового образца</a></H3>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=module value=services>
<input type=hidden name=dbaction value=apply>
<input type=hidden name=action value=ad_apply>
<input type=hidden name=table value={$query_table}>
{foreach from=$row item=item key=key name=outer}
<input type=hidden name=old[{$key}] value="{$item.value}">
{/foreach}
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY>
<TR><TD class=left>Клиент:</TD><TD>{$fixclient}</TD></TR>
{foreach from=$row key=key item=item name=outer}
{dbmap_element}
{/foreach}

<TR><TD class=left>(можно скопировать некоторые поля отсюда)</TD><TD>
<SELECT onchange="eval(this.value);">
{foreach from=$copy key=key item=item}
<option value='form.row_price.value="{$item.price}"; form.row_description.value="{$item.description}"; form.row2_period.value="{$item.period}"; {if $item.period=="once"}form.row_actual_to.value=form.row_actual_from.value;{else}form.row_actual_to.value="2029-01-01";{/if}'>{$item.description} | {$item.price}</option>
{/foreach}
</SELECT></TD></TR>
</TBODY></TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Добавить"></DIV>
</form>