{literal}
<script type="text/javascript">
function onchange_tarif(select, input)
{
	var option = select.options[select.selectedIndex];
	if (option && option.attributes && option.attributes.getNamedItem('payment'))
		input.value = option.attributes.getNamedItem('payment').value;
}
function onchange_use_group()
{
	var group = '';
	if (document.getElementById('local_mob_in_group').checked) group+='5';
	if (document.getElementById('russia_in_group').checked) group+='1';
	if (document.getElementById('intern_in_group').checked) group+='2';
	if (document.getElementById('sng_in_group').checked) group+='3';
	document.getElementById('dest_group').value = group;
	checkbox_to_show_minpayment();
}
function dest_group_to_checkbox()
{
	var group = document.getElementById('dest_group').value;
	document.getElementById('local_mob_in_group').checked = false;
	document.getElementById('russia_in_group').checked = false;
	document.getElementById('intern_in_group').checked = false;
	document.getElementById('sng_in_group').checked = false;
	for(i in group){
		if (group[i] == '5') document.getElementById('local_mob_in_group').checked = true;
		if (group[i] == '1') document.getElementById('russia_in_group').checked = true;
		if (group[i] == '2') document.getElementById('intern_in_group').checked = true;
		if (group[i] == '3') document.getElementById('sng_in_group').checked = true;
	}
}
function checkbox_to_show_minpayment()
{
	$groups = false;

	if (document.getElementById('local_mob_in_group').checked)
	{
		document.getElementById('minpayment_local_mob').type='hidden';
		$groups = true;
	}else
		document.getElementById('minpayment_local_mob').type='text';

	if (document.getElementById('russia_in_group').checked)
	{
		document.getElementById('minpayment_russia').type='hidden';
		$groups = true;
	}else
		document.getElementById('minpayment_russia').type='text';

	if (document.getElementById('intern_in_group').checked)
	{
		document.getElementById('minpayment_intern').type='hidden';
		$groups = true;
	}else
		document.getElementById('minpayment_intern').type='text';

	if (document.getElementById('sng_in_group').checked)
	{
		document.getElementById('minpayment_sng').type='hidden';
		$groups = true;
	}else
		document.getElementById('minpayment_sng').type='text';

	if ($groups)
		$('#tr_group').show();
	else
		$('#tr_group').hide();

}
function init_minpayments()
{
	if (document.getElementById('minpayment_group').value == '') 
		document.getElementById('minpayment_group').value = '0';
	if (document.getElementById('minpayment_local_mob').value == '') 
		onchange_tarif(document.getElementById('t_id_tarif_local_mob'), document.getElementById('minpayment_local_mob'));
	if (document.getElementById('minpayment_russia').value == '') 
		onchange_tarif(document.getElementById('t_id_tarif_russia'), document.getElementById('minpayment_russia'));
	if (document.getElementById('minpayment_intern').value == '') 
		onchange_tarif(document.getElementById('t_id_tarif_intern'), document.getElementById('minpayment_intern'));
	if (document.getElementById('minpayment_sng').value == '') 
		onchange_tarif(document.getElementById('t_id_tarif_sng'), document.getElementById('minpayment_sng'));
}
$(document).ready(function(){
	dest_group_to_checkbox();
	checkbox_to_show_minpayment();
	init_minpayments();
});
</script>

{/literal}
<table>
	<tr>
		<th colspan="2" align="left">Тариф:</th>
		<th align="left" width="60"></th>
		<th align="left">Гарантированный платеж</th>
	</tr>
	<tr>
		<td>Тип тарифа:</td>
		<td><select onchange="optools.friendly.voip.change_type(this)">
			<option value='public'{if $dbform_f_tarif_current.status eq 'public'} selected='selected'{/if}>Публичный</option>
			<option value='archive'{if $dbform_f_tarif_current.status eq 'archive'} selected='selected'{/if}>Архивный</option>
			<option value='special'{if $dbform_f_tarif_current.status eq 'special'} selected='selected'{/if}>Специальный</option>
		</select></td>
		<td colspan="2">
		</td>
	</tr>
	<tr>
		<td nowrap>Тариф Местные стационарные:</td>
		<td>
			<select id='t_id_tarif_public' name='dbform[t_id_tarif]'>
				<option value=0>-- выберите тариф --</option>
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_2}
					{if $tarif.status eq 'public' and $tarif.dest == '4'}
					<option	value={$tarif.id}{if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id_tarif} selected{/if}>
						{$tarif.name} ({$tarif.month_number}-{$tarif.month_line})
					</option>
					{/if}
				{/foreach}
			</select>
			<select id='t_id_tarif_archive' name='' >
				<option value=0>-- выберите тариф --</option>
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_2}
					{if $tarif.status eq 'archive' and $tarif.dest == '4'}
					<option	value={$tarif.id}{if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id_tarif} selected{/if}>
						{$tarif.name} ({$tarif.month_number}-{$tarif.month_line})
					</option>
					{/if}
				{/foreach}
			</select>
			<select id='t_id_tarif_special' name='' style='display:none'>
				<option value=0>-- выберите тариф --</option>
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_2}
					{if $tarif.status eq 'special' and $tarif.dest == '4'}
					<option	value={$tarif.id}{if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id_tarif} selected{/if}>
						{$tarif.name} ({$tarif.month_number}-{$tarif.month_line})
					</option>
					{/if}
				{/foreach}
			</select>

		</td>
		<td colspan="2"></td>
	</tr>
	<tr>
		<td nowrap>Тариф Местные мобильные:</td>
		<td>
			<select id=t_id_tarif_local_mob name=dbform[t_id_tarif_local_mob] style='display:"";width:270px' onchange="onchange_tarif(this, document.getElementById('minpayment_local_mob'))">
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_russia}
					{if $tarif.dest == '5'}
					<option	payment="{$tarif.month_min_payment}" value={$tarif.id} {if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id_tarif_local_mob} selected{/if}>
						{$tarif.name} ({$tarif.month_min_payment})
					</option>
					{/if}
				{/foreach}
			</select>
		</td>
		<td><input id="local_mob_in_group" type="checkbox" onchange="onchange_use_group()"/>Набор</td>
		<td><input id="minpayment_local_mob" type="hidden" name="dbform[t_minpayment_local_mob]" value="{$dbform_f_tarif_current.minpayment_local_mob}"/></td>
	</tr>
	<tr>
		<td nowrap>Тариф Россия:</td>
		<td>
			<select id=t_id_tarif_russia name=dbform[t_id_tarif_russia] style='display:"";width:270px' onchange="onchange_tarif(this, document.getElementById('minpayment_russia'))">>
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_russia}
					{if $tarif.dest == '1'}
					<option	payment="{$tarif.month_min_payment}"	value={$tarif.id} {if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id_tarif_russia} selected{/if}>
						{$tarif.name} ({$tarif.month_min_payment})
					</option>
					{/if}
				{/foreach}
			</select>
		</td>
		<td><input id="russia_in_group" type="checkbox" onchange="onchange_use_group()"/>Набор</td>
		<td><input id="minpayment_russia" type="hidden" name="dbform[t_minpayment_russia]" value="{$dbform_f_tarif_current.minpayment_russia}"/></td>
	</tr>
	<tr>
		<td nowrap>Тариф Международка:</td>
		<td>
			<select id=t_id_tarif_intern name=dbform[t_id_tarif_intern] style='display:"";width:270px' onchange="onchange_tarif(this, document.getElementById('minpayment_intern'))">>
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_intern}
					{if $tarif.dest == '2'}
					<option	payment="{$tarif.month_min_payment}"	value={$tarif.id} {if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id_tarif_intern} selected{/if}>
						{$tarif.name} ({$tarif.month_min_payment})
					</option>
					{/if}
				{/foreach}
			</select>
		</td>
		<td><input id="intern_in_group" type="checkbox" onchange="onchange_use_group()"/>Набор</td>
		<td><input id="minpayment_intern" type="hidden" name="dbform[t_minpayment_intern]" value="{$dbform_f_tarif_current.minpayment_intern}"/></td>
	</tr>
	<tr>
		<td nowrap>Тариф СНГ:</td>
		<td>
			<select id=t_id_tarif_sng name=dbform[t_id_tarif_sng] style='display:"";width:270px' onchange="onchange_tarif(this, document.getElementById('minpayment_sng'))">>
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_sng}
					{if $tarif.dest == '3'}
					<option	payment="{$tarif.month_min_payment}"	value={$tarif.id} {if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id_tarif_sng} selected{/if}>
						{$tarif.name} ({$tarif.month_min_payment})
					</option>
					{/if}
				{/foreach}
			</select>
		</td>
		<td><input id="sng_in_group" type="checkbox" onchange="onchange_use_group()"/>Набор</td>
		<td><input id="minpayment_sng" type="hidden" name="dbform[t_minpayment_sng]" value="{$dbform_f_tarif_current.minpayment_sng}"/></td>
	</tr>
	<tr id="tr_group">
		<td colspan="2"></td>
		<td>Набор:</td>
		<td>
			<input id="dest_group" type="hidden" name="dbform[t_dest_group]" value="{$dbform_f_tarif_current.dest_group}"/>
			<input id="minpayment_group" type="text" name="dbform[t_minpayment_group]" value="{$dbform_f_tarif_current.minpayment_group}"/>
		</td>
	</tr>
	<tr>
		<td>Дата активации:</td>
		<td>
			<input type=text class=text name=dbform[t_date_activation] value={if $dbform_f_tarif_current}{$dbform_f_tarif_current.date_activation}{else}{$smarty.now|date_format:"%Y-%m-01"}{/if}>
		</td>
		<td сщдызфт=Э2Э></td>
	</tr>
	<tr><td colspan="4"><input type="checkbox" name="dbform[t_apply_for_all_tarif_id]" value="{$dbform_f_tarif_current.id_tarif}"/>Применить для всех услуг с тарифом "{$dbform_f_tarifs[$dbform_f_tarif_current.id_tarif].name}"</td></tr>
</table><br>
<h3>Группа тарификации (priceId):</h3>
<table width=400>
<tr><td width=120>priceId:</td><td><input style='width:40px' type=text class=text name=dbform[t2_priceid] value="{if isset($dbform_f_tarif2_current)}{$dbform_f_tarif2_current.id_tarif}{/if}"></td></tr>
{if $dbform_f_tarif_current}
<tr><td>priceId в тарифе:</td><td><input style='width:40px' disabled type=text class=text value="{$dbform_f_tarif_current.priceid}"></td></tr>
{/if}
<tr><td>Дата активации:</td><td><input type=text class=text name=dbform[t2_date_activation] value={if isset($dbform_f_tarif2_current)}{$dbform_f_tarif2_current.date_activation}{else}{$smarty.now|date_format:"%Y-%m-01"}{/if}></td></tr>
</table><br>
<script type="text/javascript">
	optools.friendly.voip.change_type('{if isset($dbform_f_tarif_current)}{$dbform_f_tarif_current.status}{else}public{/if}')
</script>