<h2>Продажи менеджера "{if $res.0.sale_channel}{$res.0.sale_channel}{elseif $res_vpbx.0.sale_channel}{$res_vpbx.0.sale_channel}{else}??????{/if}" за {$ts|mdate:"месяц Y"} года</h2>
{if $res}
	<h3>Номера</h3>
	<table class="price">
		<tr>
			<th colspan=2>
				Клиент
			</th>
			<th>
				Номер
			</th>
			<th>
				Количество линий
			</th>
			<th>
				Тариф
			</th>
			<th>
				Работает с
			</th>
			<th>
				Работает по
			</th>
		</tr>
		{assign var="total" value=0}
		{assign var="total_lines" value=0}
		{assign var="is_new" value=$res.0.is_new}
		<tr>
			<td colspan=7 align="center">
				<b>{if $is_new}Новые{else}Допродажи{/if}</b>
			</td>
		</tr>
		{foreach from=$res item="p" name="outer"}
			{if $is_new && !$p.is_new}
				<tr>
					<th colspan=2>
						<b>Всего новых</b>
					</th>
					<td align="right">
						{$total}
					</td>
					<td align="right">
						{$total_lines}
					</td>
					<td colspan=2>
						&nbsp;
					</td>
				<tr>
				<tr>
					<td colspan=7 align="center">
						<b>Допродажи</b>
					</td>
				</tr>
				{assign var="is_new" value=0}
				{assign var="total" value=0}
				{assign var="total_lines" value=0}
			{/if}
			{assign var="total" value=$total+1}
			<tr class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
				<td>
					<a href="?module=clients&id={$p.client_id}">{$p.client_id}</a>
				</td>
				<td>
					<a href="?module=clients&id={$p.client}">{$p.client}</a>
				</td>
				<td>
					{$p.phone}
				</td>
				<td align="right">
					{$p.no_of_lines}{assign var="total_lines" value=$total_lines+$p.no_of_lines}
				</td>
				<td>
					{$p.tarif}
				</td>
				<td>
					{$p.actual_from|mdate:"d месяца Y"}
				</td>
				<td>
					{if $p.actual_to == "--"}{$p.actual_to}{else}{$p.actual_to|mdate:"d месяца Y"}{/if}
				</td>
			</tr>
		{/foreach}
		<tr>
			<th colspan=2>
				<b>Всего {if !$is_new}допродаж{else}новых{/if}</b>
			</th>
			<td align="right">
				{$total}
			</td>
			<td align="right">
				{$total_lines}
			</td>
			<td colspan=2>
				&nbsp;
			</td>
		<tr>
	</table>
{/if}
{if $res_vpbx}
	<h3>ВАТС</h3>
	<table class="price">
		<tr>
			<th colspan=2>
				Клиент
			</th>
			<th>
				ВАТС ID
			</th>
			<th>
				Тариф
			</th>
			<th>
				Работает с
			</th>
			<th>
				Работает по
			</th>
		</tr>
		{assign var="total" value=0}
		{assign var="is_new" value=$res_vpbx.0.is_new}
		<tr>
			<td colspan=6 align="center">
				<b>{if $is_new}Новые{else}Допродажи{/if}</b>
			</td>
		</tr>
		{foreach from=$res_vpbx item="p" name="outer"}
			{if $is_new && !$p.is_new}
				<tr>
					<th colspan=2>
						<b>Всего новых</b>
					</th>
					<td align="right">
						{$total}
					</td>
					<td colspan=2>
						&nbsp;
					</td>
					<tr>
						<td colspan=6 align="center">
							<b>Допродажи</b>
						</td>
					</tr>
				<tr>
				{assign var="is_new" value=0}
				{assign var="total" value=0}
			{/if}
			{assign var="total" value=$total+1}
			<tr class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
				<td>
					<a href="?module=clients&id={$p.client_id}">{$p.client_id}</a>
				</td>
				<td>
					<a href="?module=clients&id={$p.client}">{$p.client}</a>
				</td>
				<td>
					{$p.id}
				</td>
				<td>
					{$p.tarif}
				</td>
				<td>
					{$p.actual_from|mdate:"d месяца Y"}
				</td>
				<td>
					{if $p.actual_to == "--"}{$p.actual_to}{else}{$p.actual_to|mdate:"d месяца Y"}{/if}
				</td>
			</tr>
		{/foreach}
		<tr>
			<th colspan=2>
				<b>Всего {if !$is_new}допродаж{else}новых{/if}</b>
			</th>
			<td align="right">
				{$total}
			</td>
			<td colspan=2>
				&nbsp;
			</td>
		<tr>
	</table>
{/if}
{if $visits}
	<h3>Выезды</h3>
	<table class="price">
		<tr>
			<th>
				Дата визита
			</th>
			<th>
				К кому
			</th>
			<th>
				Заявка
			</th>
		</tr>
		{foreach from=$visits item=v}
			<tr>
				<td>
					{$v.date|mdate:"d месяца Y"}
				</td>
				<td>
					<a href="?module=clients&id={$v.client}">{$v.client}</a>
				</td>
				<td>
					<a href="./?module=tt&action=view&id={$v.id}">{$v.id}</a>
				</td>
			</tr>
		{/foreach}
	</table>
{/if}