{literal}
<script type="text/javascript">
	function change_type(el){
		var type = el.value || el, types=['public','archive'], sel;
		for(var i=0;i<2;i++){
			sel = document.getElementById('t_id_tarif_'+types[i]);
			if(type==types[i]){
				sel.style.display = 'block';
				sel.name='dbform[t_id_tarif]';
			}else{
				sel.name='';
				sel.style.display = 'none';
			}
		}
	};


</script>
{/literal}
<table>
	<tr>
		<th colspan="2" align="left">Тариф:</th>
		<th align="left" width="60"></th>
	</tr>
	<tr>
		<td>Тип тарифа:</td>
		<td><select onchange="change_type(this)" id="s_tarif_type">
			<option value='public'{if $dbform_f_tarif_current.status eq 'public'} selected='selected'{/if}>Публичный</option>
			<option value='archive'{if $dbform_f_tarif_current.status eq 'archive'} selected='selected'{/if}>Архивный</option>
		</select></td>
		<td></td>
	</tr>
	<tr>
		<td nowrap>Тариф:</td>
		<td>
			<select id='t_id_tarif_public' name='dbform[t_id_tarif]'>
				<option value=0>-- выберите тариф --</option>
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_2}
					{if $tarif.status eq 'public'}
					<option	value={$tarif.id}{if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id} selected{/if}>
						{$tarif.description}
					</option>
					{/if}
				{/foreach}
			</select>
			<select id='t_id_tarif_archive' name='' >
				<option value=0>-- выберите тариф --</option>
				{foreach from=$dbform_f_tarifs item=tarif name=tarif_2}
					{if $tarif.status eq 'archive'}
					<option	value={$tarif.id}{if isset($dbform_f_tarif_current) and $tarif.id==$dbform_f_tarif_current.id} selected{/if}>
						{$tarif.description}
					</option>
					{/if}
				{/foreach}
			</select>
		</td>
		<td></td>
	</tr>
	<tr>
		<td>Дата активации:</td>
		<td>
			<input type=text class=text name=dbform[t_date_activation] value={if $dbform_f_tarif_current}{$dbform_f_tarif_current.date_activation}{else}{$smarty.now|date_format:"%Y-%m-01"}{/if}>
		</td>
		<td></td>
	</tr>
</table><br>
<script type="text/javascript">
	change_type('{if isset($dbform_f_tarif_current)}{$dbform_f_tarif_current.status}{else}public{/if}');
</script>
