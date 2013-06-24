<H2>Письма клиентам</H2>
<H3>Список писем</H3>
<a href='{$LINK_START}module=mail&action=view'>Добавить письмо</a><br>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<TBODY><TR>
  <TD class=header vAlign=bottom width="30%">Тема</TD>
  <TD class=header vAlign=bottom width="50%">Текст</TD>
  <TD class=header vAlign=bottom width="10%">Отослано</TD>
  <TD class=header vAlign=bottom width="10%">&nbsp;</TD>
  </TR>
{foreach from=$mail_job item=r name=outer}
<TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
	<TD><a href='{$LINK_START}module=mail&action=view&id={$r.job_id}'>{$r.template_subject|htmlspecialchars}</a></TD>
	<TD style='font-size:85%'>{$r.template_body|htmlspecialchars}</TD>
	<TD>{if $r.job_state=='PM'}PM {/if}{$r.cnt_sent} / {$r.cnt_total}</TD>
	<TD><a href='{$LINK_START}module=mail&action=remove&id={$r.job_id}'>удалить</a></TD>
</TR>
{/foreach}
</TBODY></TABLE>
