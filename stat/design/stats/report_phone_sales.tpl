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
                <b class="click" onclick="phone_sales_details('numbers', '{$r.id}', '0', 'nums');">{$curr_phones[$r.id].count_num|num_format}</b>
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
                <b class="click" onclick="phone_sales_details('numbers', '{$r.id}', '0', 'no_nums');">{$curr_no_nums[$r.id].count_num|num_format}</b>
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
                <b class="click" onclick="phone_sales_details('vpbx','{$r.id}', '0');">{$curr_vpbx[$r.id]|num_format}</b>
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
            <b class="click" onclick="phone_sales_details('numbers', '{$r.id}', '0', '8800');">{$curr_8800[$r.id].count_num|num_format}</b>
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
                <b class="click" onclick="phone_sales_details('sums','{$r.id}', '0');">{if $region_sums[$r.id]}&nbsp;{/if}{$region_sums[$r.id]|num_format:true}</b>
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
                    <span class="click" onclick="phone_sales_details('numbers','{$r.id}', '{$month}', 'nums');">{$sale_nums[$r.id].all|num_format}</span>
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
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', 'no_nums');">{$sale_nonums[$r.id].all|num_format}</span>
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
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}');">{$sale_lines[$r.id].all|num_format}</span>
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
                    <span class="click" onclick="phone_sales_details('vpbx','{$r.id}', '{$month}');">{$sale_vpbx[$r.id].all|num_format}</span>
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
                    <span class="click" onclick="phone_sales_details('numbers','{$r.id}', '{$month}', '8800');">{$sale_8800[$r.id].all|num_format}</span>
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
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', 'nums', '1');">{$del_nums[$r.id]|num_format}</span>
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
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', 'no_nums', '1');">{$del_nonums[$r.id]|num_format}</span>
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
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', '', '1');">{$del_lines[$r.id]|num_format}</span>
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
                    <span class="click" onclick="phone_sales_details('vpbx', '{$r.id}', '{$month}', '', '1');">{$del_vpbx[$r.id]|num_format}</span>
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
                <span class="click" onclick="phone_sales_details('numbers', '{$r.id}', '{$month}', '8800', '1');">{$del_8800[$r.id]|num_format}</span>
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
                <span class="click" onclick="phone_sales_details('sums','{$r.id}', '{$month}');">{if $region_sums[$r.id]}&nbsp;{/if}{$region_sums[$r.id]|num_format:true}</span>
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
  <table class="price">
    <tr>
      <th rowspan="2">Продажи номеров/линий без номера по менеджерам</th>
      <th colspan="4" style="text-align: center;">Новые</th>
      <th colspan="4" style="text-align: center;">Допродажи</th>
      <th rowspan="2" >Выезды</th>
      <th rowspan="2">%</th>   
      <th colspan="4">Продажи ВАТС</th>
    </tr>
    <tr>
      <th>шт</th>
      <th>%</th>
      <th>СЛ, шт.</th>
      <th>%</th>
      <th>шт</th>
      <th>%</th>
      <th>СЛ, шт.</th>
      <th>%</th>
      <th>Новые, шт</th>
      <th>%</th>
      <th>Доп, шт.</th>
      <th>%</th>
    </tr>
    {foreach from=$sale_channels.managers item=sales key=manager name="outer"}
    <tr class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
      <td><span class="click" onclick="phone_sales_details('channels', '', '{$month}', '', '', '{$sales.sale_channel_id}');">{$manager|default:"???????"}</span></td>
      <td class="dig">
        {if isset($sales.nums.new)}
          <b>{$sales.nums.new}</b>
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.nums_perc.new)}
          {$sales.nums_perc.new}%
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.lines.new)}
          <b>{$sales.lines.new}</b>
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.lines_perc.new)}
          {$sales.lines_perc.new}%
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.nums.old)}
          <b>{$sales.nums.old}</b>
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.nums_perc.old)}
          {$sales.nums_perc.old}%
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.lines.old)}
          <b>{$sales.lines.old}</b>
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.lines_perc.old)}
          {$sales.lines_perc.old}%
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.visits)}
          <b>{$sales.visits}</b>
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.visits_perc)}
          {$sales.visits_perc}%
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.vpbx.new)}
          <b>{$sales.vpbx.new}</b>
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.vpbx_perc.new)}
          {$sales.vpbx_perc.new}%
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.vpbx.old)}
          <b>{$sales.vpbx.old}</b>
        {else}
            &nbsp;
        {/if}
      </td>
      <td class="dig">
        {if isset($sales.vpbx_perc.old)}
          {$sales.vpbx_perc.old}%
        {else}
            &nbsp;
        {/if}
      </td>
    </tr>
    {/foreach}
  </table>
{/foreach}
<div id="report_details" title="Подробная информация" style="display: none;"></div>
<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
<script>
{literal}
	function phone_sales_details(type, region, month, subtype, disabled, channel_id)
	{
		$('a.ui-dialog-titlebar-close').click();
		subtype = subtype || '';
		disabled = disabled || 0;
		channel_id = channel_id || 0;
		$("#report_details").html('');
		$("#report_details").dialog(
		{
			width: 850,
			height: 400,
			open: function(){
				$(this).load('./index_lite.php?module=stats&action=phone_sales_details&type=' + type + '&region=' + region + '&month=' + month + '&subtype=' + subtype + '&disabled=' + disabled + '&channel_id=' + channel_id);
			}
		});
	}
{/literal}
</script>