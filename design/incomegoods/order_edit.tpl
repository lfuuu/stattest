<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />
<script src="bootstrap/js/bootstrap.min.js"></script>
{include file='utils/error_modal.tpl'}


<h4>
	<small><a href="?module=incomegoods&action=order_list">Заказы</a> - </small>
	Заказ поставщику
	<a href="?module=incomegoods&action=order_view&id={$order->id}"><span class="{if $order->active}icon_active{elseif $order->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
		{$order->number}</a>

	<small><a href="?module=incomegoods&action=order_edit&id={$order->id}"><span class="icon_edit"></span>
			<span style="color: darkred">{if $order->id}Редактирование{else}Создание{/if}</span></a></small>
</h4>

<form id="ajaxForm" action="?module=incomegoods&action=order_save" method="POST">
	<input type="hidden" name="id" value="{$order->id}" />


	<table class="table table-bordered table-condensed table-hover pull-left" style="width: 500px; margin-right: 10px;">
		<!--tr>
			<th>Проведение</th>
			<td>
				<select name="active" class="form-control input-sm">
					<option value="1" {if $order->active==1}selected{/if}>Проведен</option>
					<option value="0" {if $order->active==0}selected{/if}>Не проведен</option>
				</select>
			</td>
		</tr>
		<tr>
			<th>Статус 1С</th>
			<td>
				<select name="status" class="form-control input-sm">
				{foreach from=$statuses key=status_id item=status_name}
					<option value="{$status_id}" {if $order->status==$status_id}selected{/if}>{$status_name}</option>
				{/foreach}
				</select>
			</td>
		</tr-->
		{if !$order->is_new_record()}
		<tr>
			<th>Номер, Дата</th>
			<td>{$order->number} от {$order->date->format('d.m.Y H:i:s')}</td>
		</tr>
		{/if}
		<tr>
			<th>Поставщик</th>
			<td>
				{$order->client_card->company}
				<input type="hidden" name="client_card_id" value="{$order->client_card_id}" />
			</td>
		</tr>
		<tr>
			<th>Номер поставщика</th>
			<td><input type="text" class="form-control input-sm" name="external_number" value="{$order->external_number}" /></td>
		</tr>
		<tr>
			<th>Дата поставщика</th>
			<td><input type="text" class="form-control input-sm datepicker" name="external_date" value="{if $order->external_date}{$order->external_date->format('d.m.Y')}{/if}" /></td>
		</tr>
		<tr>
			<th>Организация</th>
			<td>
                {if !$order->is_new_record()}
                    {$order->organization->name}
                {else}
				<select name="organization_id" class="form-control input-sm">
					{foreach from=$organizations item=org}
						<option value="{$org->id}" {if $order->organization_id==$org->id}selected{/if}>{$org->name}</option>
					{/foreach}
				</select>
                //
                {/if}

			</td>
		</tr>
		<tr>
			<th>Менеджер</th>
			<td>
				<select name="manager_id" class="form-control input-sm">
					{foreach from=$users item=user}
						<option value="{$user->id}" {if $order->manager_id==$user->id}selected{/if}>{$user->name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>Склад</th>
			<td>
				<select name="store_id" class="form-control input-sm">
					{foreach from=$stores item=store}
						<option value="{$store->id}" {if $order->store_id==$store->id}selected{/if}>{$store->name}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<th>Валюта</th>
			<td>
                <select name="currency" class="form-control input-sm">
                    {foreach from=$currencies item=currency}
                        <option value="{$currency->id}" {if $order->currency==$currency->id}selected{/if}>{$currency->id}</option>
                    {/foreach}
                </select>
			</td>
		</tr>
		<tr>
			<th>Цена включает НДС</th>
			<td>
				<input type="checkbox" name="price_includes_nds" {if $order->price_includes_nds}checked{/if}>
			</td>
		</tr>
		<tr>
			<th>Сумма Итого</th>
			<th class="sum_itog">{$order->sum}</th>
		</tr>
	</table>

	<table class="table table-bordered table-condensed table-hover pull-left" style="width: 250px; margin-right: 10px;">
		<caption><h5 class="text-left">Документы поступления:</h5></caption>
		{foreach from=$order->documents item=item}
			<tr>
				<td><a href="?module=incomegoods&action=document_view&id={$item->id}">
						<span class="{if $item->deleted}icon_deleted_disabled{elseif $item->active}icon_active{else}icon_disabled{/if}"></span>
						{$item->number} от {$item->date->format('d.m.Y H:i:s')}
					</a></td>
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
			<th>Цена</th>
			<th>Сумма <small>{if $order->price_includes_nds}(Вкл. НДС){else}(Без НДС){/if}</small></th>
			<th>Сумма НДС</th>
			<th>Ожидаемая<br/>дата поступления</th>
		</tr>
		</thead>
		<tbody id="goods_body">
		{foreach from=$order->lines key=pos item=item}
			<tr id="good_id_{$item->good->num_id}">
				<td>{$item->good->num_id}
					<input type="hidden" name="item[{$pos}][line_code]" value="{$item->line_code}" />
					<input type="hidden" name="item[{$pos}][good_id]" value="{$item->good_id}" />
				</td>
				<td>{$item->good->name}</td>
				<td>
					<input type="text" name="item[{$pos}][amount]" class="form-control input-sm amount" value="{$item->amount}" autocomplete="off"/>
				</td>
				<td>{$item->getDocumentAmount()}</td>
				<td>{$item->getStoreAmount()}</td>
				<td>
					<input type="text" name="item[{$pos}][price]" class="form-control input-sm price" value="{$item->price}" />
				</td>
				<td class="sum">{$item->sum}</td>
				<td class="sum_nds">{$item->sum_nds}</td>
				<td>
					<input type="text" name="item[{$pos}][incoming_date]" class="form-control input-sm datepicker incoming_date" value="{$item->incoming_date->format('d.m.Y')}" />
				</td>
			</tr>
		{/foreach}
		</tbody>
		<tfoot id="goods_foot">
		<tr>
			<th colspan="6">Итого</th>
			<th class="sum">0</th>
			<th class="sum_nds">0</th>
			<th>&nbsp;</th>
		</tr>
		</tfoot>
	</table>


	<table class="table table-bordered table-condensed table-hover" id="searchGoods">
		<thead>
		<caption><input type="text" class="form-control input-sm good_search_field" placeholder="Добавить товар..." value="" /></caption>
		</thead>
		<tbody class="good_search_body">
		</tbody>
	</table>

	<input id="ajaxFormSubmit" type="button" class="btn btn-primary" value="Сохранить">
</form>

<script>
	var price_includes_nds = {if $order->price_includes_nds}true{else}false{/if};
	{literal}

	(function(){

		statlib.prepareAjaxSubmittingForm('ajaxForm', 'ajaxFormSubmit');

		$('.datepicker').datepicker({dateFormat:'dd.mm.yy'});

		var calc_nds = function(sum) {
			price_includes_nds
			if (price_includes_nds) {
				return 18*sum/118;
			} else {
				return sum*0.18;
			}
		}

		var calc_totals = function(){
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

		$('#goods_body').on('change, keyup', '.amount, .price', function(){
			var tr = $(this).parent().parent();
			calc_row(tr);
		});

		$('form [name=price_includes_nds]').on('change', function(){
			price_includes_nds = this.checked;
			calc_all();
		})

		calc_all();

		var $field = $('.good_search_field');
		var $body = $('.good_search_body');

		var addGood = function(good){
			var $good = $('#good_id_'+good.num_id);
			if ($good.length > 0) {
				var field_amount = $good.find('.amount');
				field_amount.val(parseFloat(field_amount.val())+1);
				var tr = $good;
			} else {
				var row_id = Math.random() * (999999 - 111111) + 111111;
				var today = new Date()

				var tr = $('<tr id="good_id_'+good.num_id+'">');
				$('<td>').text(good.num_id).appendTo(tr)
						.append($('<input type="hidden" name="item['+row_id+'][line_code]" value="0" />'))
						.append($('<input type="hidden" name="item['+row_id+'][good_id]" value="'+good.id+'" />'));
				$('<td>').text(good.name).appendTo(tr);
				$('<td>').appendTo(tr)
						.append($('<input type="text" name="item['+row_id+'][amount]" class="form-control input-sm  amount" value="1" />'));
				$('<td>').text('0').appendTo(tr);
				$('<td>').text('0').appendTo(tr);
				$('<td>').appendTo(tr)
						.append($('<input type="text" name="item['+row_id+'][price]" class="form-control input-sm price" value="0" />'))
						.append($('<span> {/literal}{$document->currency}{literal}</span>'));
				$('<td class="sum">').text('0').appendTo(tr);
				$('<td class="sum_nds">').text('0').appendTo(tr);
				$('<td>').appendTo(tr)
						.append($('<input type="text" name="item['+row_id+'][incoming_date]" class="form-control input-sm datepicker incoming_date" />')
								.val(today.getDate()+'.'+today.getMonth()+'.'+today.getFullYear())
								.datepicker({dateFormat:'dd.mm.yy'}));
				$('#goods_body').append(tr);
			}
			tr
					.stop()
					.css({backgroundColor: '#00DD00'})
					.animate({backgroundColor: '#FFFFFF'}, 1000, function(){
						tr.css({backgroundColor: 'none'});
					})
			;
		}

		var search_timer = undefined;
		var search_ajax = undefined;


		var search = function(query) {
			clearTimeout(search_timer);

			search_timer = setTimeout(function(){
				if (search_ajax) search_ajax.abort();
				search_ajax = $.ajax({
					type: "POST",
					url: "?module=data&action=search_goods",
					data: { query: query }
				}).always(function() {
							search_ajax = undefined;
				}).done(function(responce) {
					$body.html('');
					$.each(responce.result, function(index, good) {
						var tr = $('<tr style="cursor: pointer">');
						tr[0].good= good;
						var query = responce.query.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
						var num_id = (''+good.num_id).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(new RegExp(query,'gi'), '<b>'+query+'</b>');
						var art = good.art.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(new RegExp(query,'gi'), '<b>'+query+'</b>');
						var name = good.name.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(new RegExp(query,'gi'), '<b>'+query+'</b>');

						$('<td>').appendTo(tr).html(num_id);
						$('<td>').appendTo(tr).html(art);
						$('<td>').appendTo(tr).html(name);
						tr.appendTo($body);
						tr.on('click', function(){
							addGood(good);
						});
					});
				});
			}, 200);


		};

		$field.on('keyup change', function(){
			search(''+$field.val());
		});
	})();

	{/literal}
</script>
