<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />

<h4>
	<small><a href="?module=incomegoods&action=order_list">Заказы</a> - </small>
	Заказ поставщику
	<a href="?module=incomegoods&action=order_view&id={$order->id}"><span class="{if $order->active}icon_active{elseif $order->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
		{$order->number}</a>

	<small><a href="?module=incomegoods&action=order_edit&id={$order->id}"><span class="icon_edit"></span>
			Редактировать</a></small>

	<small><a href="?module=incomegoods&action=document_edit&id=&order_id={$order->id}"><span class="icon_add"></span>
			Создать поступление</a></small>
</h4>

<table class="table table-bordered table-condensed table-hover pull-left" style="width: 500px; margin-right: 10px;">
	<tr>
		<th>Статус 1С</th>
		<td><strong>{$order->status}</strong></td>
	</tr>
	<tr>
		<th>Номер, Дата</th>
		<td>{$order->number} от {$order->date->format('d.m.Y H:i:s')}</td>
	</tr>
	<tr>
		<th>Поставщик</th>
		<td>{$order->client_card->company}</td>
	</tr>
	<tr>
		<th>По данным поставщика</th>
		<td>{$order->external_number} {if $order->external_date} от {$order->external_date->format('d.m.Y H:i:s')}{/if}</td>
	</tr>
	<tr>
		<th>Организация</th>
		<td>{$order->organization->name}</td>
	</tr>
	<tr>
		<th>Менеджер</th>
		<td>{$order->manager->name}</td>
	</tr>
	<tr>
		<th>Склад</th>
		<td>{$order->store->name}</td>
	</tr>
	<tr>
		<th>Сумма</th>
		<td>{$order->sum} {$order->currency}</td>
	</tr>
	{if $order->comment}
		<tr>
			<th>Комментарий</th>
			<td>{$order->comment}</td>
		</tr>
	{/if}
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
	<tbody>
	{foreach from=$order->lines item=item}
		<tr>
			<td>{$item->good->num_id}</td>
			<td>{$item->good->name}</td>
			<td>{$item->amount}</td>
			<td>{$item->getDocumentAmount()}</td>
			<td>{$item->getStoreAmount()}</td>
			<td>{$item->price}</td>
			<td>{$item->sum}</td>
			<td>{$item->sum_nds}</td>
			<td>{$item->incoming_date->format('d.m.Y')}</td>
		</tr>
	{/foreach}
	</tbody>

</table>