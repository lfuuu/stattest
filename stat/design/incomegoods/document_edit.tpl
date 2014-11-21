<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<script src="bootstrap/js/bootstrap.min.js"></script>
{include file='utils/error_modal.tpl'}

<h4>
	<small><a href="?module=incomegoods&action=order_list">Заказы</a> - </small>
	<small>Заказ
		<a href="?module=incomegoods&action=order_view&id={$order->id}"><span class="{if $order->active}icon_active{elseif $order->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
			{$order->number}</a> -
	</small>
	Поступление товаров
	<a href="?module=incomegoods&action=document_view&id={$document->id}"><span class="{if $document->active}icon_active{elseif $document->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
		{$document->number}</a>

	<small><a href="?module=incomegoods&action=document_edit&id={$document->id}"><span class="icon_edit"></span>
			<span style="color: darkred">{if $document->id}Редактирование{else}Создание{/if}</span></a></small>
</h4>

<form id="ajaxForm" action="?module=incomegoods&action=document_save" method="POST">
	<input type="hidden" name="id" value="{$document->id}" />
	<input type="hidden" name="order_id" value="{$order->id}" />

	<table class="table table-bordered table-condensed table-hover pull-left" style="width: 500px; margin-right: 10px;">
		<tr>
			<th>Проведение</th>
			<td>
				<select name="active" class="form-control input-sm">
					<option value="1" {if $document->active==1}selected{/if}>Проведен</option>
					<option value="0" {if $document->active==0}selected{/if}>Не проведен</option>
				</select>
			</td>
		</tr>
		{if !$document->is_new_record()}
		<tr>
			<th>Номер, Дата</th>
			<td>{$document->number} от {$document->date->format('d.m.Y H:i:s')}</td>
		</tr>
		{/if}
		<tr>
			<th>Поставщик</th>
			<td>{$document->client_card->company}</td>
		</tr>
		<tr>
			<th>Организация</th>
			<td>{$document->organization->name}</td>
		</tr>
		<tr>
			<th>Склад</th>
			<td>{$document->store->name}</td>
		</tr>
		<tr>
			<th>Валюта</th>
			<td>{$document->currency}</td>
		</tr>
		<tr>
			<th>Сумма Итого</th>
			<th class="sum_itog">{$document->sum}</th>
		</tr>
		{if $document->comment}
			<tr>
				<th>Комментарий</th>
				<td>{$document->comment}</td>
			</tr>
		{/if}
	</table>

	<table class="table table-bordered table-condensed table-hover pull-left" style="width: 250px; margin-right: 10px;">
		<caption><h5 class="text-left">Документы поступления:</h5></caption>
		{foreach from=$order->documents item=item}
			<tr>
				{if $document->id != $item->id}
					<td><a href="?module=incomegoods&action=document_view&id={$item->id}">
							<span class="{if $item->deleted}icon_deleted_disabled{elseif $item->active}icon_active{else}icon_disabled{/if}"></span>
							{$item->number} от {$item->date->format('d.m.Y H:i:s')}
						</a></td>
				{else}
					<td>
						<span class="{if $item->deleted}icon_deleted_disabled{elseif $item->active}icon_active{else}icon_disabled{/if}"></span>
						{$item->number} от {$item->date->format('d.m.Y H:i:s')}
					</td>
				{/if}
			</tr>
		{/foreach}
	</table>

	<table class="table table-bordered table-condensed table-hover pull-left" style="width: 250px">
		<caption><h5 class="text-left">Приходные ордера:</h5></caption>
		{foreach from=$order->stores item=item}
			<tr>
				<td><a href="?module=incomegoods&action=store_view&id={$item->id}">
						<span class="{if $item->deleted}icon_deleted_disabled{elseif $item->active}icon_active{else}icon_disabled{/if}"></span>
						{$item->number} от {$item->date->format('d.m.Y H:i:s')}
					</a></td>
			</tr>
		{/foreach}
	</table>

	<div class="clearfix"></div>

	<table class="table table-bordered table-condensed table-hover">
		<thead>
		<caption><h5 class="text-left">Товары:</h5></caption>
		<tr>
			<th>Код</th>
			<th>Наименование</th>
			<th>Заказано</th>
			<th>Поступило</th>
			<th>Оприходовано</th>
			<th>Количество</th>
			<th>Цена</th>
			<th>Сумма <small>{if $document->price_includes_nds}(Вкл. НДС){else}(Без НДС){/if}</small></th>
			<th>Сумма НДС</th>
			<th>Номер ГТД</th>
		</tr>
		</thead>
		<tbody id="goods_body">
		{foreach from=$lines item=item}
			<tr {if $item.line_code == 0}danger{/if}">
				<td>{$item.good_num_id}
					<input type="hidden" name="item[{$item.pos}][line_code]" value="{$item.line_code}" />
					<input type="hidden" name="item[{$item.pos}][good_id]" value="{$item.good_id}" />
				</td>
				<td>
					{if $item.line_code == 0}<b>(Позиция отсутствует в заказе)</b><br/>{/if}
					{$item.good_name}
				</td>
				<td>{if $item.order_line}{$item.order_line->amount}{/if}</td>
				<td>{if $item.order_line}{$item.order_line->getDocumentAmount()}{/if}</td>
				<td>{if $item.order_line}{$item.order_line->getStoreAmount()}{/if}</td>
				<td>
					<input type="text" name="item[{$item.pos}][amount]" class="form-control input-sm amount" value="{$item.amount}" autocomplete="off"/>
				</td>
				<td>
					<input type="hidden" name="item[{$item.pos}][price]" class="price" value="{if $item.order_line}{$item.order_line->price}{else}{$item.price}{/if}" />
					{if $item.order_line}{$item.order_line->price}{else}{$item.price}{/if}
				</td>
				<td class="sum">{$item.sum}</td>
				<td class="sum_nds">{$item.sum_nds}</td>
				<td>
					<input type="hidden" name="item[{$item.pos}][gtd_id]" class="gtd_id" value="{if $item.gtd}{$item.gtd->id}{/if}" />
					<input type="text" class="form-control input-sm gtd_code" value="{if $item.gtd}{$item.gtd->code}{/if}" />
					<span class="gtd_country">{if $item.gtd}{$item.gtd->country->name}{/if}</span>
				</td>
			</tr>
		{/foreach}
		</tbody>
		<tfoot id="goods_foot">
		<tr>
			<th colspan="7">Итого</th>
			<th class="sum">0</th>
			<th class="sum_nds">0</th>
			<th>&nbsp;</th>
		</tr>
		</tfoot>
	</table>

	<input id="ajaxFormSubmit" type="button" class="btn btn-primary" value="Сохранить">
</form>

<div id="addGtdModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addGtdModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="addGtdModalLabel">Добавление Номера ГТД</h3>
			</div>
			<div class="modal-body">
				<p>Номер ГТД не найден. Добавить Его?</p>
				<input type="text" id="add_gtd_code" class="form-control input-sm" value="" /> <br/><br/>
				<select id="add_gtd_country" class="form-control input-sm">
					<option value="">----- Выберите страну -----</option>
					{foreach from=$countries item=country}
						<option value="{$country->code}">{$country->name}</option>
					{/foreach}
				</select>
			</div>
			<div class="modal-footer">
				<button id="add_gtd_button" class="btn btn-primary">Добавить</button>
				<button class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>

		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
var price_includes_nds = {if $document->price_includes_nds}true{else}false{/if};
{literal}
(function(){

	statlib.prepareAjaxSubmittingForm('ajaxForm', 'ajaxFormSubmit');

	var calc_nds = function(sum) {
		if (price_includes_nds) {
			return 18*sum/118;
		} else {
			return sum*0.18;
		}
	}

	var calc_totals = function() {
		var sum = 0;
		$.each($('#goods_body tr'), function(i, tr) {
			sum += parseFloat($(tr).find('.sum').text());
		});
		var sum_nds = calc_nds(sum);
		var sum_itog = price_includes_nds ? sum : sum + sum_nds;
		var tr = $('#goods_foot tr')
		tr.find('.sum').text(sum.toFixed(2));
		tr.find('.sum_nds').text(sum_nds.toFixed(2));
		$('.sum_itog').text(sum_itog.toFixed(2));
	}

	var calc_row = function(tr){
		var amount = parseFloat(tr.find('.amount').val());
		var price = parseFloat(tr.find('.price').val());
		var sum = amount*price;
		var sum_nds = calc_nds(sum);
		tr.find('.sum').text(sum.toFixed(2));
		tr.find('.sum_nds').text(sum_nds.toFixed(2));

		calc_totals();
	}

	var calc_all = function(){
		$('#goods_body tr').each(function(i, tr){
			calc_row($(tr));
		});
	}

	calc_all();

	$('#goods_body').on('change, keyup', '.amount, .price', function(){
		var tr = $(this).parent().parent();
		calc_row(tr);
	});
	$('#goods_body').on('change', '.gtd_code', function(){

		var td = $(this).parent();
		var $gtd_code = td.find('.gtd_code');
		var $gtd_id = td.find('.gtd_id');
		var $gtd_country = td.find('.gtd_country');
		var gtd_code = $gtd_code.val();
		$.ajax({
			type: "POST",
			url: "?module=data&action=get_gtd",
			data: { code: gtd_code }
		}).done(function(data) {
			if (typeof(data) == 'string') {
				showErrorModal(data);
			} else if (data.id) {
				$gtd_id.val(data.id);
				$gtd_code.val(data.code);
				$gtd_country.text(data.country);
			} else {
				$gtd_id.val('');
				$gtd_code.val('');
				$gtd_country.text('');
				var modal = $('#addGtdModal')[0];
				modal.$gtd_id = $gtd_id;
				modal.$gtd_code = $gtd_code;
				modal.$gtd_country = $gtd_country;
				$('#add_gtd_code').val(gtd_code);
				$('#addGtdModal').appendTo(document.body).modal();
			}
		});


	});

	$('#add_gtd_button').on('click', function(){
		$('#add_gtd_button').attr('disabled', 'disabled');
		$.ajax({
			type: "POST",
			url: "?module=incomegoods&action=add_gtd",
			data: { code: $('#add_gtd_code').val(), country: $('#add_gtd_country').val() }
		}).done(function(data) {
			if (typeof(data) == 'string') {
				showErrorModal(data);
			} else if (data.id) {
				var modal = $('#addGtdModal')[0];
				modal.$gtd_id.val(data.id);
				modal.$gtd_code.val(data.code);
				modal.$gtd_country.text(data.country);
			}
		}).fail(function(data) {
			showErrorModal('Не удалось создать ГТД');
		}).always(function(data) {
			$('#add_gtd_button').removeAttr('disabled');
			$('#addGtdModal').modal('hide');
		});
	});

})();
{/literal}
</script>
