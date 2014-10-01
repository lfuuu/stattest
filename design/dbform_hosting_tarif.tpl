<h3>Тариф:</h3>
<table width=50%>
<tr><td width=35%>Тариф:</td><td>
<select id=t_id_tarif name=dbform[t_id_tarif] style='display:""'>
<option value=0>тариф не выбран</option>
<option value=0></option>
{foreach from=$dbform_f_tarifs item=tarif name=tarif_2}
<option value={$tarif.id}{if $tarif.id==$dbform_f_tarif_current.id_tarif} selected{/if}>{$tarif.name} ({$tarif.pay_month}$-{$tarif.mb_disk}MB)</option>
{/foreach}</select>
</td></tr>
<tr><td>Дата активации:</td><td><input type=text class=text name=dbform[t_date_activation] value={if $dbform_f_tarif_current}{$dbform_f_tarif_current.date_activation}{else}{$smarty.now|date_format:"%Y-%m-01"}{/if}></td></tr>
</table><br>
