<style>
{literal}
.dig {
    text-align: right;
}
.head_tr {
    background-color: #eee;
    font-weight: bold;
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
    <td><b>Подключено номеров</b></td>
    {foreach from=$regions item=r}
        <td class="dig"><b>{$curr_phones[$r.id].count_num}</b></td>
    {/foreach}
  </tr>
  <tr>
    <td><b>Подключено линий (СЛ)</b></td>
    {foreach from=$regions item=r}
        <td class="dig"><b>{$curr_phones[$r.id].count_lines}</b></td>
    {/foreach}
  </tr>
</table>

{foreach from=$reports item=report}
  {assign var=sale_nums value=$report.sale_nums}
  {assign var=sale_nonums value=$report.sale_nonums}
  {assign var=sale_lines value=$report.sale_lines}
  {assign var=sale_clients value=$report.sale_clients}
  {assign var=sale_channels value=$report.sale_channels}
  {assign var=del_nums value=$report.del_nums}
  {assign var=del_nonums value=$report.del_nonums}
  {assign var=del_lines value=$report.del_lines}
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
      <td>Всего подано номеров</td>
        {foreach from=$regions item=r}
            <td class="dig">{$sale_nums[$r.id].all}</td>
        {/foreach}
        <td class="dig">{$sale_nums.all.all}</td>
    </tr>
    <tr>
      <td>из них новая продажа</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_nums[$r.id].new}</td>
        {/foreach}
      <td class="dig">{$sale_nums.all.new}</td>
    </tr>
    <tr>
      <td>из них допродажа</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_nums[$r.id].old}</td>
        {/foreach}
      <td class="dig">{$sale_nums.all.old}</td>
    </tr>
    <tr class="head_tr">
      <td>Всего подано линий без номера</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_nonums[$r.id].all}</td>
        {/foreach}
      <td class="dig">{$sale_nonums.all.all}</td>
    </tr>
    <tr>
      <td>из них новая продажа</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_nonums[$r.id].new}</td>
        {/foreach}
      <td class="dig">{$sale_nonums.all.new}</td>
    </tr>
    <tr>
      <td>из них допродажа</td>
        {foreach from=$regions item=r}
      <td class="dig">{$sale_nonums[$r.id].old}</td>
        {/foreach}
      <td class="dig">{$sale_nonums.all.old}</td>
    </tr>
    <tr class="head_tr">
      <td>Всего подано соединительных линий</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_lines[$r.id].all}</td>
        {/foreach}
      <td class="dig">{$sale_lines.all.all}</td>
    </tr>
    <tr>
      <td>из них новая продажа</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_lines[$r.id].new}</td>
        {/foreach}
      <td class="dig">{$sale_lines.all.new}</td>
    </tr>
    <tr>
      <td>из них допродажа</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_lines[$r.id].old}</td>
        {/foreach}
      <td class="dig">{$sale_lines.all.old}</td>
    </tr>
    <tr class="head_tr">
      <td>Всего клиентов телефонии</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_clients[$r.id].all}</td>
        {/foreach}
      <td class="dig">{$sale_clients.all.all}</td>
    </tr>
    <tr>
      <td>из них новых</td>
        {foreach from=$regions item=r}
          <td class="dig">{$sale_clients[$r.id].new}</td>
        {/foreach}
      <td class="dig">{$sale_clients.all.new}</td>
    </tr>
    <tr>
      <td>из них допродажа</td>
            {foreach from=$regions item=r}
          <td class="dig">{$sale_clients[$r.id].old}</td>
        {/foreach}
      <td class="dig">{$sale_clients.all.old}</td>
    </tr>
    <tr class="head_tr">
      <td>Отключено номеров</td>
            {foreach from=$regions item=r}
          <td class="dig">{$del_nums[$r.id]}</td>
        {/foreach}
      <td class="dig">{$del_nums.all}</td>
    </tr>
    <tr class="head_tr">
      <td>Отключено линий без номера</td>
        {foreach from=$regions item=r}
          <td class="dig">{$del_nonums[$r.id]}</td>
        {/foreach}
      <td class="dig">{$del_nonums.all}</td>
    </tr>
    <tr class="head_tr">
      <td>Отключено соединительных линий</td>
        {foreach from=$regions item=r}
          <td class="dig">{$del_lines[$r.id]}</td>
        {/foreach}
      <td class="dig">{$del_lines.all}</td>
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
    <tr>
    {foreach from=$sale_channels.managers item=sales key=manager}
    <tr>
      <td>{$manager|default:"???????"}</td>
      <td class="dig"><b>{$sales.nums.new}</b></td>
      <td class="dig">{$sales.nums_perc.new}%</td>
      <td class="dig"><b>{$sales.lines.new}</b></td>
      <td class="dig">{$sales.lines_perc.new}%</td>
      <td class="dig"><b>{$sales.nums.old}</b></td>
      <td class="dig">{$sales.nums_perc.old}%</td>
      <td class="dig"><b>{$sales.lines.old}</b></td>
      <td class="dig">{$sales.lines_perc.old}%</td>
      <td class="dig"><b>{$sales.visits}</b></td>
      <td class="dig">{$sales.visits_perc}%</td>
    </tr>
    {/foreach}
  </table>
{/foreach}
