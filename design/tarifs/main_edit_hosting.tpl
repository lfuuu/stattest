{assign var=f value='has_dns'}
<TR><TD class=left width=40%>{$tarifs_translate.$f}</TD>
<TD><input type=checkbox name={$f} value=1 {if $tarifs_data.$f==1}checked{/if}></TD></TR>
{assign var=f value='has_ftp'}
<TR><TD class=left width=40%>{$tarifs_translate.$f}</TD>
<TD><input type=checkbox name={$f} value=1 {if $tarifs_data.$f==1}checked{/if}></TD></TR>
{assign var=f value='has_ssh'}
<TR><TD class=left width=40%>{$tarifs_translate.$f}</TD>
<TD><input type=checkbox name={$f} value=1 {if $tarifs_data.$f==1}checked{/if}></TD></TR>
{assign var=f value='has_ssi'}
<TR><TD class=left width=40%>{$tarifs_translate.$f}</TD>
<TD><input type=checkbox name={$f} value=1 {if $tarifs_data.$f==1}checked{/if}></TD></TR>
{assign var=f value='has_php'}
<TR><TD class=left width=40%>{$tarifs_translate.$f}</TD>
<TD><input type=checkbox name={$f} value=1 {if $tarifs_data.$f==1}checked{/if}></TD></TR>
{assign var=f value='has_perl'}
<TR><TD class=left width=40%>{$tarifs_translate.$f}</TD>
<TD><input type=checkbox name={$f} value=1 {if $tarifs_data.$f==1}checked{/if}></TD></TR>
{assign var=f value='has_mysql'}
<TR><TD class=left width=40%>{$tarifs_translate.$f}</TD>
<TD><input type=checkbox name={$f} value=1 {if $tarifs_data.$f==1}checked{/if}></TD></TR>
<TR><TD class=left width=40%>{$tarifs_translate.status}</TD>
<TD><select class=text name=status>
<option value='public'{if $tarifs_data.status=='public'} selected{/if}>public</option>
<option value='special'{if $tarifs_data.status=='special'} selected{/if}>special</option>
<option value='archive'{if $tarifs_data.status=='archive'} selected{/if}>archive</option>
</select></TD></TR>
<TR><TD class=left width=40%>{$tarifs_translate.currency}</TD>
<TD><select class=text name=currency>
<option value='USD'{if $tarifs_data.currency!='RUR'} selected{/if}>USD</option>
<option value='RUR'{if $tarifs_data.currency=='RUR'} selected{/if}>RUR</option>
</select></TD></TR>
