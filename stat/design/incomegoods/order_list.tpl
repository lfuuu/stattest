<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" />

<h4><a href="?module=incomegoods&action=order_list">Заказы поставщику</a></h4>

<ul class="nav nav-pills">
	<form id="filter" action="" method="GET" class="navbar-form pull-left" style="margin-right: 15px;">
		<input type="hidden" name="module" value="incomegoods"/>
		<input type="hidden" name="action" value="order_list"/>
		<input id="filter_state" type="hidden" name="filter[state]" value="{$qfilter.state}"/>
		<select class="select2" name="filter[manager]" onchange="$('#filter').submit(); return false;" style="width: 250px">
			<option value="all">--- Менеджер ---</option>
			{foreach from=$users item=user}
				<option value="{$user->id}" {if $user->id==$qfilter.manager}selected{/if}>{$user->name}</option>
			{/foreach}
		</select>
        &nbsp;&nbsp;
        <select class="select2" name="filter[organization]" onchange="$('#filter').submit(); return false;" style="width: 170px">
            <option value="all">--- Организация ---</option>
            {foreach from=$organizations item=org}
                <option value="{$org->id}" {if isset($qfilter.organization) && $org->id==$qfilter.organization}selected{/if}>{$org->name}</option>
            {/foreach}
        </select>
	</form>

	{foreach from=$statesCounter key=key item=state}
		{if $state.count > 0}
		<li {if $key==$qfilter.state}class="active"{/if}><a href="#filter" onclick="$('#filter_state').val('{$key}');$('#filter').submit(); return false;">{$state.name} <span class="badge">{$state.count}</span></a></li>
		{/if}
	{/foreach}
</ul>
<p></p>
<table class="table table-bordered table-condensed table-hover">
	<thead>
		<tr>
			<th>Номер</th>
			<th>Дата</th>
			<th>Статус</th>
			<th>Номер Вх.</th>
			<th>Сумма <small>(с НДС)</small></th>
			<th>Валюта</th>
			<th>Контрагент</th>
			<th>Менеджер</th>
		</tr>
	</thead>
	<tbody>
	{foreach from=$list item=item}
		<tr>
			<td><a href="?module=incomegoods&action=order_view&id={$item->id}">
					<span class="{if $item->active}icon_active{elseif $item->deleted}icon_deleted_disabled{else}icon_disabled{/if}"></span>
					{$item->number}
			</a></td>
			<td><a href="?module=incomegoods&action=order_view&id={$item->id}">{$item->date->format('d.m.Y')}</a></td>
			<td>{$item->state_name}</td>
			<td>{if $item->external_number}{$item->external_number}{if $item->external_date} от {$item->external_date->format('d.m.Y')}{/if}{else}&nbsp;{/if}</td>
			<td>{$item->sum}</td>
			<td>{$item->currency}</td>
			<td>{$item->client_card->company}</td>
			<td>{if $item->manager}{$item->manager->name}{else}-{/if}</td>
		</tr>
	{/foreach}
	</tbody>
</table>
