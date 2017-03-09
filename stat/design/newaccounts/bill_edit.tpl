
<ul class="breadcrumb">
	<li>
		<a href="/">Главная</a>
	</li>
	<li>
		<a href="/client/view?id={$bill.client_id}">Аккаунт: {$bill.client_id}</a>
	</li>
	<li>
		<a href="/?module=newaccounts&action=bill_view&bill={$bill.bill_no}">Счет №{$bill.bill_no}</a>
	</li>
	<li>
		<a href="?module=newaccounts&action=bill_edit&bill={$bill.bill_no}">Редактирование</a>
	</li>

</ul>

<h2>Бухгалтерия {$fixclient}</h2>
<H3>Редактирование проводки</H3>
<form action="?" method=post id=form name=form>
	<input type=hidden name=module value=newaccounts>
	<input type=hidden name=bill value={$bill.bill_no}>
	<input type=hidden name=action value=bill_apply>
	<input type=hidden name=client_id value={$bill.client_id}>

    <div class="well">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Дата проводки:</label>
                    <input class="form-control input-sm" type=text id=bill_date_from name=bill_date
                           value="{$bill_date}">
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label>Валюта проводки: </label>
                    <input class="form-control input-sm" type=text value="{$bill.currency}" readonly>
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label>Предполагаемый тип платежа: </label>
                    <select name="nal" class="form-control">
                        <option value="beznal"{if $bill.nal=="beznal"} selected{/if}>безнал</option>
                        <option value="nal"{if $bill.nal=="nal"} selected{/if}>нал</option>
                        <option value="prov"{if $bill.nal=="prov"} selected{/if}>пров</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Дата оплаты счета:</label>
                    <input class="form-control input-sm" type=text id=pay_bill_until name=pay_bill_until
                           value="{$pay_bill_until}">
                </div>
            </div>

            {if $show_bill_no_ext || access('newaccounts_bills', 'edit_ext')}
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>Внешний счет: </label>
                        <input class="form-control input-sm" type=text name=bill_no_ext value="{$bill.bill_no_ext}">
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label>Дата внешнего счета: <input type="checkbox" value="Y"
                                                           name="date_from_active" {if $bill.bill_no_ext_date} checked{/if}
                                                           onchange="activateDatePicker(this);"> </label>
                        <div class="form-group ">
                            <input class="form-control input-sm" {if !$bill.bill_no_ext_date} disabled="disabled"{/if}
                                   id=date_from type=text name=bill_no_ext_date
                                   value="{if $bill.bill_no_ext_date}{"d-m-Y"|date:$bill.bill_no_ext_date}{/if}">

                        </div>
                    </div>
                </div>
            {else}
                <div class="col-sm-8">&nbsp;</div>
            {/if}
        </div>

        <div class="row">
            <div class="col-sm-8">
                <div class="form-group">
                    <label>Исполнитель:</label>
                    {html_options name='courier' options=$l_couriers selected=$bill.courier_id}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    <label>
                        Цена включает НДС
                        <input type="checkbox" value="Y"
                               name="price_include_vat" {if $bill.price_include_vat > 0} checked{/if}>
                    </label>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-11"></div>
            <div class="col-sm-1">
                <input id='submit' class='btn btn-primary' type='submit' value="Сохранить">
            </div>
        </div>
    </div>

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
		<input id='submit' class='btn btn-primary' type='submit' value="Сохранить">
	</div>
</form>

<script>
	{literal}

    $('#bill_date_from').datepicker({
        dateFormat: 'dd-mm-yy',
        maxDate: $('#pay_bill_until').val(),
        onClose: function (selectedDate) {
            $('#pay_bill_until').datepicker('option', 'minDate', selectedDate);
        }
    });

    $('#pay_bill_until').datepicker({
        dateFormat: 'dd-mm-yy',
        minDate: $('#bill_date_from').val(),
        onClose: function (selectedDate) {
            $('#bill_date_from').datepicker('option', 'maxDate', selectedDate);
        }
    });


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