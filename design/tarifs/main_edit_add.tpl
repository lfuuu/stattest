<TR><TD class=left width=40%>Период</TD>
<TD><select style='width:100%' name='period'>
<option value='day'{if $tarifs_data.period=='day'} selected{/if}>ежедневно</option>
<option value='month'{if $tarifs_data.period=='month'} selected{/if}>ежемесячно</option>
<option value='year'{if $tarifs_data.period=='year'} selected{/if}>ежегодно</option>
<option value='once'{if $tarifs_data.period=='once'} selected{/if}>единажды</option>
</select></TD></TR>
<TR><TD class=left width=40%>{$tarifs_translate.currency}</TD>
<TD><select class=text name=currency>
<option value='USD'{if $tarifs_data.currency=='USD'} selected{/if}>USD</option>
<option value='RUR'{if $tarifs_data.currency!='USD'} selected{/if}>RUR</option>
</select></TD></TR>
