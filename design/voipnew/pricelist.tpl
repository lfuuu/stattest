<form action="./index.php" method="get" style="display:inline">
	<input type="hidden" name="module" value="voipnew" />
	<input type="hidden" name="action" value="pricelist" />
	<select name="f_group_id">
	{foreach from=$ogroups item='g'}
	<option value="{$g.id}" {if $g.id eq $f_group_id} selected{/if}>{$g.name}</option>
	{/foreach}
	</select>
	<select name="f_dest_group">
	<option value="-1" {if $f_dest_group eq '-1'} selected{/if}>-- Все направления --</option>
	<option value="0" {if $f_dest_group eq '0'} selected{/if}>Москва</option>
	<option value="1" {if $f_dest_group eq '1'} selected{/if}>Россия</option>
	<option value="2" {if $f_dest_group eq '2'} selected{/if}>Международка</option>
	<option value="3" {if $f_dest_group eq '3'} selected{/if}>СНГ</option>
	</select>
	<select name="f_country_id">
	<option value="0">-- Все страны --</option>
	{foreach from=$countries item='g'}
	<option value="{$g.id}" {if $g.id eq $f_country_id} selected{/if}>{$g.name}</option>
	{/foreach}
	</select>
	<select name="f_region_id">
	<option value="0">-- Все регионы --</option>
	{foreach from=$regions item='g'}
	<option value="{$g.id}" {if $g.id eq $f_region_id} selected{/if}>{$g.name}</option>
	{/foreach}
	</select>


	<input type=submit name="make" value="Сформировать"/>
	<input type=submit name="export" value="Выгрузить"/>
	
	<div style="margin-bottom:5px;margin-left:700px;">
	<label><input type=radio name=f_profit value=1 {if $f_profit eq '1'} checked{/if} />за 1 месяц</label>
	<label><input type=radio name=f_profit value=6 {if $f_profit eq '6'} checked{/if} />за 6 месяцев</label>
	</div>
</form>

<div id='popup_price' style="position:absolute;display:none;background-color:#FFFFFF;border:1px #BBBBBB solid;padding: 3px;"></div>


<table class=price cellSpacing=2 cellPadding=4 border=0>
<tr><td class=header rowspan=2>Префикс номера</td><td class=header rowspan=2>Назначение</td>
	{foreach from=$operators item='o'}
		<td class=header colspan=3>Цена <b>{$o.name}</b></td>
	{/foreach}
	<td class=header rowspan=2>Лучшая цена</td>
	<td class=header rowspan=2>Объем<br/>(мин)</td>
	<td class=header colspan=2>Прибыль</td>
	<td class=header colspan=3><b>Наша цена</b></td>
</tr>
<tr>
	{foreach from=$operators item='o'}
		<td class=header>прошл.</td>
		<td class=header>тек.</td>
		<td class=header>буд.</td>
	{/foreach}
		<td class=header>прошл.</td>
		<td class=header>буд.</td>
		<td class=header>прошл.</td>
		<td class=header>тек.</td>
		<td class=header>буд.</td>
</tr>
{foreach from=$report item='rd'}
{if $rd.operators[999].price_before != '' && $rd.operators[999].price_before != $rd.operators[999].price }
<tr class={cycle values='even,odd'}>
	<td>{$rd.defcode}</td>
	<td>{$rd.destination}</td>
	{foreach from=$operators item='o'}
		<td onmousemove="statlib.modules.voip.show_price2(event, '{$o.name}', '{$rd.operators[$o.id].eff_defcode_before}', '{$rd.operators[$o.id].date_before}', '{$rd.operators[$o.id].diff_before}', '{$rd.operators[$o.id].price_before}', '{$rd.operators[$o.id].date_from}', '{$rd.operators[$o.id].price}', '{$rd.operators[$o.id].date_after}', '{$rd.operators[$o.id].diff_after}', '{$rd.operators[$o.id].price_after}');" onmouseout="statlib.modules.voip.hide_price(event)">
			{if $rd.operators[$o.id].diff_before > 0}+{$rd.operators[$o.id].diff_before}%{elseif $rd.operators[$o.id].diff_before < 0}{$rd.operators[$o.id].diff_before}%{/if}</td>
		<td onmousemove="statlib.modules.voip.show_price2(event, '{$o.name}', '{$rd.operators[$o.id].eff_defcode}', '{$rd.operators[$o.id].date_before}', '{$rd.operators[$o.id].diff_before}', '{$rd.operators[$o.id].price_before}', '{$rd.operators[$o.id].date_from}', '{$rd.operators[$o.id].price}', '{$rd.operators[$o.id].date_after}', '{$rd.operators[$o.id].diff_after}', '{$rd.operators[$o.id].price_after}');" onmouseout="statlib.modules.voip.hide_price(event)"
			{if $o.id eq $rd.best_op}style="background-color:#deffde"{/if}>{$rd.operators[$o.id].price}</td>
		<td onmousemove="statlib.modules.voip.show_price2(event, '{$o.name}', '{$rd.operators[$o.id].eff_defcode_after}', '{$rd.operators[$o.id].date_before}', '{$rd.operators[$o.id].diff_before}', '{$rd.operators[$o.id].price_before}', '{$rd.operators[$o.id].date_from}', '{$rd.operators[$o.id].price}', '{$rd.operators[$o.id].date_after}', '{$rd.operators[$o.id].diff_after}', '{$rd.operators[$o.id].price_after}');" onmouseout="statlib.modules.voip.hide_price(event)">
			{if $rd.operators[$o.id].diff_after > 0}+{$rd.operators[$o.id].diff_after}%{elseif $rd.operators[$o.id].diff_after < 0}{$rd.operators[$o.id].diff_after}%{/if}</td>
	{/foreach}
	<td>{$rd.best_price}</td>
	<td>{math equation="x/60" x=$rd.volume_mcn format="%d"}</td>
	<td>{$rd.profit_before}</td>
	<td>{$rd.profit_after}</td>

		<td onmousemove="statlib.modules.voip.show_price2(event, '{$o.name}', '{$rd.operators[$o.id].eff_defcode_before}', '{$rd.operators[$o.id].date_before}', '{$rd.operators[$o.id].diff_before}', '{$rd.operators[$o.id].price_before}', '{$rd.operators[$o.id].date_from}', '{$rd.operators[$o.id].price}', '{$rd.operators[$o.id].date_after}', '{$rd.operators[$o.id].diff_after}', '{$rd.operators[$o.id].price_after}');" onmouseout="statlib.modules.voip.hide_price(event)">
			{$rd.operators[999].price_before}</td>
		<td onmousemove="statlib.modules.voip.show_price2(event, '{$o.name}', '{$rd.operators[$o.id].eff_defcode}', '{$rd.operators[$o.id].date_before}', '{$rd.operators[$o.id].diff_before}', '{$rd.operators[$o.id].price_before}', '{$rd.operators[$o.id].date_from}', '{$rd.operators[$o.id].price}', '{$rd.operators[$o.id].date_after}', '{$rd.operators[$o.id].diff_after}', '{$rd.operators[$o.id].price_after}');" onmouseout="statlib.modules.voip.hide_price(event)"
			>{$rd.operators[999].price}</td>
		<td onmousemove="statlib.modules.voip.show_price2(event, '{$o.name}', '{$rd.operators[$o.id].eff_defcode_after}', '{$rd.operators[$o.id].date_before}', '{$rd.operators[$o.id].diff_before}', '{$rd.operators[$o.id].price_before}', '{$rd.operators[$o.id].date_from}', '{$rd.operators[$o.id].price}', '{$rd.operators[$o.id].date_after}', '{$rd.operators[$o.id].diff_after}', '{$rd.operators[$o.id].price_after}');" onmouseout="statlib.modules.voip.hide_price(event)">
			{$rd.operators[999].price_after}</td>
</tr>
{/if}
{/foreach}
</table>


