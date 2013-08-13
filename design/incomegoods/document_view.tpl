<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />

<h4>
	<small><a href="?module=incomegoods&action=order_list">Заказы</a> - </small>
	<small>Заказ
		<a href="?module=incomegoods&action=order_view&id={$order->id}"><span class="{if $order->active}icon_active{elseif $order->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
			{$order->number}</a> -
	</small>
	Поступление товаров
	<a href="?module=incomegoods&action=document_view&id={$document->id}">
		<span class="{if $document->active}icon_active{elseif $document->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
		{$document->number}
	</a>

	<small><a href="?module=incomegoods&action=document_edit&id={$document->id}"><span class="icon_edit"></span>
			Редактировать</a></small>

	<small><a href="?module=incomegoods&action=document_edit&id=&order_id={$order->id}"><span class="icon_add"></span>
			Создать поступление</a></small>
</h4>


<table class="table table-bordered table-condensed table-hover pull-left" style="width: 500px; margin-right: 10px;">
	<tr>
		<th>Номер, Дата</th>
		<td>{$document->number} от {$document->date->format('d.m.Y H:i:s')}</td>
	</tr>
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
		<th>Сумма</th>
		<td>{$document->sum} {$document->currency}</td>
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
					<span class="{if $item->active}icon_active{elseif $item->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
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
		<th>Номер ГТД</th>
	</tr>
	</thead>
	<tbody>
	{foreach from=$lines item=item}
		{if $item.amount > 0}
		<tr style="{if $item.line_code == 0}background-color: #f5acc3{/if}">
			<td>{$item.good_num_id}</td>
			<td>{$item.good_name}</td>
			<td>{if $item.order_line}{$item.order_line->amount}{/if}</td>
			<td>{if $item.order_line}{$item.order_line->getDocumentAmount()}{/if}</td>
			<td>{if $item.order_line}{$item.order_line->getStoreAmount()}{/if}</td>
			<td>{$item.amount}</td>
			<td>{if $item.order_line && $item.price!=$item.order_line->price}class="danger"{/if}
				{$item.price}
				{if $item.order_line > 0 && $item.price!=$item.order_line->price} / {$item.order_line->price}{/if}
			</td>
			<td>{$item.sum}</td>
			<td>
				{if $item.gtd}
					{$item.gtd->code} ({$item.gtd->country->name})
				{/if}

			</td>
		</tr>
		{/if}
	{/foreach}
	</tbody>

</table>