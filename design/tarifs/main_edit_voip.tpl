<TR><TD class=left width=40%>{$tarifs_translate.period}</TD>
<TD><select class=text name=period>
<option value='month'{if $tarifs_data.period=='month'} selected{/if}>ежемесячно</option>
<option value='day'{if $tarifs_data.period=='day'} selected{/if}>ежедневно</option>
<option value='year'{if $tarifs_data.period=='year'} selected{/if}>ежегодно</option>
<option value='immediately'{if $tarifs_data.period=='immediately'} selected{/if}>немедленно</option>
</select></TD></TR>
<TR><TD class=left width=40%>{$tarifs_translate.status}</TD>
<TD><select class=text name=status>
<option value='public'{if $tarifs_data.status=='public'} selected{/if}>публичный</option>
<option value='special'{if $tarifs_data.status=='special'} selected{/if}>специальный</option>
<option value='archive'{if $tarifs_data.status=='archive'} selected{/if}>архивный</option>
<option value='operator'{if $tarifs_data.status=='operator'} selected{/if}>оператор</option>
</select></TD></TR>
<TR><TD class=left width=40%>{$tarifs_translate.currency}</TD>
<TD><select class=text name=currency>
<option value='USD'{if $tarifs_data.currency=='USD'} selected{/if}>USD</option>
<option value='RUR'{if $tarifs_data.currency<>'USD'} selected{/if}>RUR</option>
</select></TD></TR>
