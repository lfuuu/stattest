<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />

<h4>
	<small><a href="?module=incomegoods&action=order_list">Заказы</a> - </small>
	<small>Заказ
		<a href="?module=incomegoods&action=order_view&id={$document->order->id}"><span class="{if $document->order->active}icon_active{elseif $document->order->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
			{$document->order->number}</a> -
	</small>
	Приходный ордер на товары
	<a href="?module=incomegoods&action=document_view&id={$document->id}"><span class="{if $document->active}icon_active{elseif $document->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
		{$document->number}</a>
</h4>


<table class="table table-bordered table-condensed table-hover pull-left" style="width: 500px; margin-right: 10px;">
	<tr>
		<th>Статус 1С</th>
		<td><strong>{$document->status}</strong></td>
	</tr>
	<tr>
		<th>Номер, Дата</th>
		<td>{$document->number} от {$document->date->format('d.m.Y H:i:s')}</td>
	</tr>
	<tr>
		<th>Склад</th>
		<td>{$document->store->name}</td>
	</tr>
	<tr>
		<th>Ответственный</th>
		<td>{$document->responsible}</td>
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
	{foreach from=$document->order->documents item=item}
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
	{foreach from=$document->order->stores item=item}
		<tr>
			{if $document->id != $item->id}
				<td><a href="?module=incomegoods&action=store_view&id={$item->id}">
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

<div class="clearfix"></div>



<table class="table table-bordered table-condensed table-hover">
	<thead>
	<caption><h5 class="text-left">Товары:</h5></caption>
	<tr>
		<th>Код</th>
		<th>Наименование</th>
		<th>Количество</th>
		<th>Серийные номера</th>
	</tr>
	</thead>
	<tbody>
	{foreach from=$document->lines item=item}
		<tr>
			<td>{$item->good->num_id}</td>
			<td>{$item->good->name}</td>
			<td>{$item->amount}</td>
			<td>
				{if $item->serial_numbers}
					<a href="#" onclick="$(this).parent().children().toggle(); return false;" style="display: block">Показать</a>
					<a href="#" onclick="$(this).parent().children().toggle(); return false;" style="display: none">Скрыть</a>
					<pre style="display: none">{$item->serial_numbers}</pre>
				{/if}
			</td>
		</tr>
	{/foreach}
	</tbody>

</table>