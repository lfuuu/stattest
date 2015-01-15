<TR><TD class=left width=40%>{$tarifs_translate.period}</TD>
<TD><select class=text name=period>
<option value='month'{if $tarifs_data.period=='month'} selected{/if}>ежемесячно</option>
<option value='day'{if $tarifs_data.period=='day'} selected{/if}>ежедневно</option>
<option value='year'{if $tarifs_data.period=='year'} selected{/if}>ежегодно</option>
<option value='immediately'{if $tarifs_data.period=='immediately'} selected{/if}>немедленно</option>
</select></TD></TR>
<TR><TD class=left width=40%>{$tarifs_translate.type_count}</TD>
<TD><select class=text name=type_count>
<option value='all'{if $tarifs_data.type_count=='all'} selected{/if}>all</option>
<option value='unlim_r1'{if $tarifs_data.type_count=='unlim_r1'} selected{/if}>unlim_r1</option>
<option value='unlim_all'{if $tarifs_data.type_count=='unlim_all'} selected{/if}>unlim_all</option>
</select></TD></TR>
<TR><TD class=left width=40%>{$tarifs_translate.status}</TD>
<TD><select class=text name=status>
<option value='public'{if $tarifs_data.status=='public'} selected{/if}>публичный</option>
<option value='special'{if $tarifs_data.status=='special'} selected{/if}>специальный</option>
<option value='archive'{if $tarifs_data.status=='archive'} selected{/if}>архивный</option>
</select></TD></TR>
<TR><TD class=left width=40%>{$tarifs_translate.currency}</TD>
<TD><select class=text name=currency>
<option value='USD'{if $tarifs_data.currency!='RUB'} selected{/if}>USD</option>
<option value='RUB'{if $tarifs_data.currency=='RUB'} selected{/if}>RUB</option>
</select></TD></TR>
