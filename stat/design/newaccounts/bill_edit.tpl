{literal}
<script type="text/javascript">

</script>
{/literal}
<h2>Бухгалтерия {$fixclient}</h2>
<H3>Редактирование проводки</H3>
<form action="?" method=post id=form name=form>
	<input type=hidden name=module value=newaccounts>
	<input type=hidden name=bill value={$bill.bill_no}>
	<input type=hidden name=action value=bill_apply>
	<input type=hidden name=client_id value={$bill.client_id}>

	Дата проводки: <input type=text id=bill_date_from name=bill_date value="{$bill_date}">
	Валюта проводки: <b style='color:blue'>{$bill.currency}</b><br>
	Исполнитель: {html_options name='courier' options=$l_couriers selected=$bill.courier_id}<br>
	Предпологаемый тип платежа:
	<select name="nal">
		<option value="beznal"{if $bill.nal=="beznal"} selected{/if}>безнал</option>
		<option value="nal"{if $bill.nal=="nal"} selected{/if}>нал</option>
		<option value="prov"{if $bill.nal=="prov"} selected{/if}>пров</option>
	</select><br>
	{if $show_bill_no_ext || access('newaccounts_bills', 'edit_ext')}
		Внешний счет: <input type=text name=bill_no_ext value="{$bill.bill_no_ext}"><br>
		Дата внешнего счета: <input {if !$bill.bill_no_ext_date} disabled="disabled"{/if} id=date_from  type=text name=bill_no_ext_date value="{if $bill.bill_no_ext_date}{"d-m-Y"|date:$bill.bill_no_ext_date}{/if}">
		<input type="checkbox" value="Y" name="date_from_active" {if $bill.bill_no_ext_date} checked{/if} onchange="activateDatePicker(this);"><br/>
	{/if}
	<label>Цена включает НДС <input type="checkbox" value="Y" name="price_include_vat" {if $bill.price_include_vat > 0} checked{/if}></label><br/>
	<br/>

	<table class="table table-condensed table-striped">
		<tr>
			<th width=1%>&#8470;</th>
			<th width=80%>Наименование</th>
			<th>Количество</th>
			<th>Цена</th>
			<th>Тип</th>
			<th>
				Удаление
				<input type="checkbox" id="mark_del" onchange="if (this.checked) $('input.mark_del').attr('checked','checked'); else $('input.mark_del').removeAttr('checked');" />
			</th>
		</tr>
		{foreach from=$bill_lines item=item key=key name=outer}
		<tr>
			<td>{$smarty.foreach.outer.iteration}.</td>
			<td><input class="form-control input-sm" value="{if isset($item.item)}{$item.item|escape:"input_value_quotes"}{/if}" name=item[{$key}]></td>
			<td><input class="form-control input-sm" style="width: 100px" value="{if isset($item.amount)}{$item.amount}{/if}" name=amount[{$key}]></td>
			<td><input class="form-control input-sm" style="width: 80px" value="{if isset($item.price)}{$item.price}{/if}" name=price[{$key}] ></td>
			<td>
				<select class="form-control input-sm" style="width: 90px" name=type[{$key}]>
					<option value='service'{if isset($item.type) && $item.type=='service'} selected{/if}>услуга &nbsp; &nbsp; &nbsp;обычная</option>
					<option value='zalog'{if isset($item.type) && $item.type=='zalog'} selected{/if}>залог &nbsp; &nbsp;&nbsp; &nbsp;&nbsp;(попадает в с/ф-3)</option>
					<option value='zadatok'{if isset($item.type) && $item.type=='zadatok'} selected{/if}>задаток &nbsp; (не попадает в с/ф)</option>
					<option value='good'{if isset($item.type) && $item.type=='good'} selected{/if}>товар</option>
				</select>
			</td>
			<td><input type="checkbox" class="mark_del" name="del[{$key}]" value="1" /></td>
		</tr>
		{/foreach}
	</table>
	<div style="text-align: center">
		<input id='submit' class='button' type='submit' value="Изменить">
	</div>
</form>

<script>
	{literal}

	optools.DatePickerInit();
	optools.DatePickerInit('bill_');

	function activateDatePicker(elm)
	{
		$('#date_from').attr('disabled', !elm.checked);
	}

	function mark_del(){
		if (document.getElementById('mark_del').checked)
			$('input.mark_del').attr('checked','checked');
		else
			$('input.mark_del').removeAttr('checked');

	}

	{/literal}
</script>