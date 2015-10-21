<style>
{literal}
.dig {
    text-align: right;
}
.click:hover {
	color: #000066;
}
.click {
	text-decoration: underline;
	cursor: pointer;
}
.head_tr {
    background-color: #eee;
    font-weight: bold;
}
.td_title {
	font-size: 10px;
}
{/literal}
</style>

<form action="?" action="POST">
    <input type="hidden" name="module" value="stats">
    <input type="hidden" name="action" value="report_phone_sales">
    <table border='0' cellpadding='0' cellspacing='3'><tr valign="middle"><td>
    Период отчёта c:&nbsp;</td><td>
    <select name='from_m'>{foreach from=$select_month item='m' key='key'}<option value='{$key}' {if $key == $from_m}selected='selected'{/if}>{$m}</option>{/foreach}</select></td><td>
    <select name='from_y'>{foreach from=$select_year item='y'}<option value='{$y}' {if $y == $from_y}selected='selected'{/if}>{$y}</option>{/foreach}</select></td><td>
    &nbsp;&nbsp;по:&nbsp;</td><td>
    <select name='to_m'>{foreach from=$select_month item='m' key='key'}<option value='{$key}' {if $key == $to_m}selected='selected'{/if}>{$m}</option>{/foreach}</select></td><td>
    <select name='to_y'>{foreach from=$select_year item='y'}<option value='{$y}' {if $y == $to_y}selected='selected'{/if}>{$y}</option>{/foreach}</select></td><td>
    &nbsp;&nbsp;<input type="submit" value="Показать"></td></tr>
    </table>
</form>
<h2>Отчёт за&nbsp;{if $from_m == $to_m && $from_y == $to_y}{$from_m|string_format:'%02d'}.{$from_y}{else}период&nbsp;&nbsp;{$from_m|string_format:'%02d'}.{$from_y}&nbsp...&nbsp;{$to_m|string_format:'%02d'}.{$to_y}{/if}</h2>
<table class="price">
  <tr>
    <th>&nbsp;</th>
    {foreach from=$regions item=r}
        <th title="{$r.name}">{$r.short_name}</th>
    {/foreach}
  </tr>
  <tr>
    <td class="td_title"><b>Подключено номеров</b></td>
    {foreach from=$regions item=r}
        <td class="dig">
            {if isset($curr_phones[$r.id].count_num)}
                <b class="click" onclick="phone_sales_details('numbers', '{$r.id}', '0', '{$year}', 'nums');">{$curr_phones[$r.id].count_num|num_format}</b>
            {else}
                &nbsp;
            {/if}
        </td>
    {/foreach}
  </tr>
  <tr>
    <td class="td_title"><b>Подключено линий (СЛ)</b></td>
    {foreach from=$regions item=r}
        <td class="dig">
            {if isset($curr_phones[$r.id].count_lines)}
                <b>{$curr_phones[$r.id].count_lines|num_format}</b>
            {else}
                &nbsp;
            {/if}
        </td>
    {/foreach}
  </tr>
  <tr>
    <td class="td_title"><b>Подключено линий без номера</b></td>
    {foreach from=$regions item=r}
        <td class="dig">
            {if isset($curr_no_nums[$r.id].count_num)}
                <b class="click" onclick="phone_sales_details('numbers', '{$r.id}', '0', '{$year}', 'no_nums');">{$curr_no_nums[$r.id].count_num|num_format}</b>
            {else}
                &nbsp;
            {/if}
        </td>
    {/foreach}
  </tr>
  <tr>
    <td class="td_title"><b>Подключено ВАТС</b></td>
    {foreach from=$regions item=r}
        <td class="dig">
            {if isset($curr_vpbx[$r.id])}
                <b class="click" onclick="phone_sales_details('vpbx','{$r.id}', '0', '{$year}');">{$curr_vpbx[$r.id]|num_format}</b>
            {else}
                &nbsp;
            {/if}
        </td>
    {/foreach}
  </tr>
  <tr>
    <td class="td_title"><b>Количество клиентов</b></td>
    {foreach from=$regions item=r}
        <td class="dig">
            {if isset($region_clients_count[$r.id])}
                <b>{$region_clients_count[$r.id]|num_format}</b>
            {else}
                &nbsp;
            {/if}
        </td>
    {/foreach}
  </tr>
  <tr>
    <td class="td_title"><b>Подключено 8800 номеров</b></td>
    {foreach from=$regions item=r}
        <td class="dig">
            {if isset($curr_8800[$r.id].count_num)}
            <b class="click" onclick="phone_sales_details('numbers', '{$r.id}', '0', '{$year}', '8800');">{$curr_8800[$r.id].count_num|num_format}</b>
            {else}
                &nbsp;
            {/if}
        </td>
    {/foreach}
  </tr>
  {if access('stats', 'vip_report')}
  {assign var=region_sums value=$reports.0.region_sums}
  <tr>
    <td class="td_title"><b>Доход по региону</b></td>
    {foreach from=$regions item=r}
        <td class="dig">
            {if isset($region_sums[$r.id])}
                <b class="click" onclick="phone_sales_details('sums','{$r.id}', '0', '{$year}');">{if $region_sums[$r.id]}&nbsp;{/if}{$region_sums[$r.id]|num_format:true}</b>
            {else}
                &nbsp;
            {/if}
        </td>
    {/foreach}
  </tr>
  {/if}
</table>

{foreach from=$reports item=report}
  {assign var=sale_nums value=$report.sale_nums}
  {assign var=sale_8800 value=$report.sale_8800}
  {assign var=region_sums value=$report.region_sums}
  {assign var=sale_nonums value=$report.sale_nonums}
  {assign var=sale_lines value=$report.sale_lines}
  {assign var=sale_clients value=$report.sale_clients}
  {assign var=sale_channels value=$report.sale_channels}
  {assign var=del_nums value=$report.del_nums}
  {assign var=del_8800 value=$report.del_8800}
  {assign var=del_nonums value=$report.del_nonums}
  {assign var=del_lines value=$report.del_lines}
  {assign var=del_vpbx value=$report.del_vpbx}
  {assign var=sale_vpbx value=$report.sale_vpbx}
  {assign var=vpbx_clients value=$report.vpbx_clients}
  {assign var=month value=$report.month}
  {assign var=year value=$report.year}
  <h2>Статистика продаж телефонных номеров {$report.date}</h2>

  <table class="price">
    <tr>
      <th>Позиция</th>
        {foreach from=$regions item=r}
            <th title="{$r.name}">{$r.short_name}</th>
        {/foreach}
      <th>Все</th>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Всего продано<br/>номеров</td>
        {foreach from=$regions item=r}
            <td class="dig">
                {if isset($sale_nums[$r.id].all)}
                    <span class="click" onclick="phone_sales_details('numbers','{$r.id}', '{$month}', '{$year}', 'nums');">{$sale_nums[$r.id].all|num_format}</span>
                {else}
                    &nbsp;
                {/if}
            </td>
        {/foreach}
        <td class="dig">{$sale_nums.all.all|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них новая продажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_nums[$r.id].new)}
                {$sale_nums[$r.id].new|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_nums.all.new|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них допродажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_nums[$r.id].old)}
                {$sale_nums[$r.id].old|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_nums.all.old|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Всего продано линий<br/>без номера</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_nonums[$r.id].all)}
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', '{$year}', 'no_nums');">{$sale_nonums[$r.id].all|num_format}</span>
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_nonums.all.all|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них новая продажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_nonums[$r.id].new)}
                {$sale_nonums[$r.id].new|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_nonums.all.new|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них допродажа</td>
        {foreach from=$regions item=r}
      <td class="dig">
        {if isset($sale_nonums[$r.id].old)}
            {$sale_nonums[$r.id].old|num_format}
        {else}
            &nbsp;
        {/if}
      </td>
        {/foreach}
      <td class="dig">{$sale_nonums.all.old|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Всего продано<br/>соединительных линий</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_lines[$r.id].all)}
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', '{$year}');">{$sale_lines[$r.id].all|num_format}</span>
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_lines.all.all|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них новая продажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_lines[$r.id].new)}
                {$sale_lines[$r.id].new|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_lines.all.new|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них допродажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
              {if isset($sale_lines[$r.id].old)}
                {$sale_lines[$r.id].old|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_lines.all.old|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Всего клиентов<br/>телефонии</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_clients[$r.id].all)}
                {$sale_clients[$r.id].all|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_clients.all.all|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них новых</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_clients[$r.id].new)}
                {$sale_clients[$r.id].new|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_clients.all.new|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них допродажа</td>
            {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_clients[$r.id].old)}
                {$sale_clients[$r.id].old|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_clients.all.old|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Всего продано ВАТС</td>
        {foreach from=$regions item=r}
            <td class="dig">
                {if isset($sale_vpbx[$r.id].all)}
                    <span class="click" onclick="phone_sales_details('vpbx','{$r.id}', '{$month}', '{$year}');">{$sale_vpbx[$r.id].all|num_format}</span>
                {else}
                    &nbsp;
                {/if}
            </td>
        {/foreach}
        <td class="dig">{$sale_vpbx.all.all|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них новая продажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_vpbx[$r.id].new)}
                {$sale_vpbx[$r.id].new|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_vpbx.all.new|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них допродажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_vpbx[$r.id].old)}
                {$sale_vpbx[$r.id].old|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_vpbx.all.old|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Всего клиентов<br/>ВАТС</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($vpbx_clients[$r.id].all)}
              {$vpbx_clients[$r.id].all|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$vpbx_clients.all.all|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них новых</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($vpbx_clients[$r.id].new)}
              {$vpbx_clients[$r.id].new|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$vpbx_clients.all.new|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них допродажа</td>
            {foreach from=$regions item=r}
          <td class="dig">
            {if isset($vpbx_clients[$r.id].old)}
              {$vpbx_clients[$r.id].old|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$vpbx_clients.all.old|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Всего продано<br/>8800 номеров</td>
        {foreach from=$regions item=r}
            <td class="dig">
                {if isset($sale_8800[$r.id].all)}
                    <span class="click" onclick="phone_sales_details('numbers','{$r.id}', '{$month}', '{$year}', '8800');">{$sale_8800[$r.id].all|num_format}</span>
                {else}
                    &nbsp;
                {/if}
            </td>
        {/foreach}
        <td class="dig">{$sale_8800.all.all|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них новая продажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_8800[$r.id].new)}
                {$sale_8800[$r.id].new|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_8800.all.new|num_format:true}</td>
    </tr>
    <tr>
      <td class="td_title">из них допродажа</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($sale_8800[$r.id].old)}
                {$sale_8800[$r.id].old|num_format}
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$sale_8800.all.old|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Отключено номеров</td>
            {foreach from=$regions item=r}
          <td class="dig">
            {if isset($del_nums[$r.id])}
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', '{$year}', 'nums', '1');">{$del_nums[$r.id]|num_format}</span>
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$del_nums.all|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Отключено линий<br/>без номера</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($del_nonums[$r.id])}
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', '{$year}', 'no_nums', '1');">{$del_nonums[$r.id]|num_format}</span>
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$del_nonums.all|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Отключено<br/>соединительных линий</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($del_nonums[$r.id])}
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', '{$year}', '', '1');">{$del_lines[$r.id]|num_format}</span>
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$del_lines.all|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Отключено ВАТС</td>
        {foreach from=$regions item=r}
            <td class="dig">
                {if isset($del_vpbx[$r.id])}
                    <span class="click" onclick="phone_sales_details('vpbx', '{$r.id}', '{$month}', '{$year}', '', '1');">{$del_vpbx[$r.id]|num_format}</span>
                {else}
                    &nbsp;
                {/if}
            </td>
        {/foreach}
      <td class="dig">{$del_vpbx.all|num_format:true}</td>
    </tr>
    <tr class="head_tr">
      <td class="td_title">Отключено 8800 номеров</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($del_8800[$r.id])}
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', '{$year}', '8800', '1');">{$del_8800[$r.id]|num_format}</span>
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{$del_8800.all|num_format:true}</td>
    </tr>
    {if access('stats', 'vip_report')}
    <tr class="head_tr">
      <td class="td_title">Доход по региону</td>
        {foreach from=$regions item=r}
          <td class="dig">
            {if isset($region_sums[$r.id])}
                <span class="click" onclick="phone_sales_details('sums','{$r.id}', '{$month}', '{$year}');">{if $region_sums[$r.id]}&nbsp;{/if}{$region_sums[$r.id]|num_format:true}</span>
            {else}
                &nbsp;
            {/if}
          </td>
        {/foreach}
      <td class="dig">{if $region_sums.all}&nbsp;{/if}{$region_sums.all|num_format:true}</td>
     {/if}
    </tr>
  </table>
  <br/>
{/foreach}


{$RManager}
<br/><br/>
{$RPartner}