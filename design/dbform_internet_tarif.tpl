<h3>Тариф:</h3>
<table width=50%>
<tr><td width=35%>Тип тарифа:</td><td><select id=t_tarif_type name=dbform[t_tarif_type] onchange=form_ip_ports_tarif()>
<option value=I{if $dbform_f_tarif_current && $dbform_f_tarif_current.type=='I'} selected{/if}>интернет (I)</option>
<option value=C{if $dbform_f_tarif_current && $dbform_f_tarif_current.type=='C'} selected{/if}>collocation (C)</option>
<option value=V{if $dbform_f_tarif_current && $dbform_f_tarif_current.type=='V'} selected{/if}>VPN (V)</option>
</select></td></tr>
<tr><td width=35%>Статус тарифа:</td><td><select id=t_tarif_status name=dbform[t_tarif_status] onchange=form_ip_ports_tarif()>
<option value=P{if $dbform_f_tarif_current && $dbform_f_tarif_current.status=='public'} selected{/if}>public</option>
<option value=S{if $dbform_f_tarif_current && $dbform_f_tarif_current.status=='special'} selected{/if}>special</option>
<option value=A{if $dbform_f_tarif_current && $dbform_f_tarif_current.status=='archive'} selected{/if}>archive</option>
<option value=Su{if $dbform_f_tarif_current && $dbform_f_tarif_current.status=='adsl_su'} selected{/if}>adsl.su</option>
<option value=Ss{if $dbform_f_tarif_current && $dbform_f_tarif_current.status=='ss'} selected{/if}>Специальный обычный</option>
<option value=Sc{if $dbform_f_tarif_current && $dbform_f_tarif_current.status=='sc'} selected{/if}>Специальный коллективный</option>
</select></td></tr>
<tr><td width=35%>Тариф:</td><td>
{foreach from=$dbform_f_tarif_types item=tariftype}
	<select id=t_id_tarif{$tariftype.2} name=dbform[t_id_tarif{$tariftype.2}] style='display:{if ($tariftype.0!="I" || $dbform_f_tarif_current.type) && ($dbform_f_tarif_current.type!=$tariftype.0 || $dbform_f_tarif_current.status!=$tariftype.1)}none{/if}'>
	<option value=0>тариф не выбран</option>
	<option value=0></option>
	{foreach from=$dbform_f_tarifs item=tarif name=tarif_2}
	{if
		($tarif.type==$tariftype.0 && $tarif.status==$tariftype.1)
	||
		($tariftype.1 eq 'ss' && $tarif.status eq 'special' && $tarif.type eq 'I' && $tarif.type_internet eq 'standard')
	||
		($tariftype.1 eq 'sc' && $tarif.status eq 'special' && $tarif.type eq 'I' && $tarif.type_internet eq 'collective')
	}
	<option value={$tarif.id}{if isset($dbform_f_tarif_current) && $tarif.id==$dbform_f_tarif_current.id_tarif} selected{/if}>{$tarif.type_internet} {$tarif.name} ({$tarif.mb_month}-{$tarif.pay_month}-{$tarif.pay_mb})</option>
	{/if}{/foreach}</select>
{/foreach}
</td></tr>
<tr><td>Дата активации:</td><td><input type=text class=text name=dbform[t_date_activation] value={if isset($dbform_f_tarif_current)}{$dbform_f_tarif_current.date_activation}{else}{$smarty.now|date_format:"%Y-%m-01"}{/if}></td></tr>
</table><br>
<script language=javascript>form_ip_ports_hide();</script>
