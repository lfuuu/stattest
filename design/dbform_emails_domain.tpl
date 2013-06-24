@<select id=domain name=dbform[domain] class=text{$_domain.add}>
{foreach from=$_domain.enum item=var name=inner}
<option value='{$var}'{if $_domain.value==$var} selected{/if}>{$var}</option>
{/foreach}
</select>