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
        <td><b>{$curr_phones[$r.id].count}</b></td>
    {/foreach}
  </tr>
</table>

{foreach from=$reports item=report}
  {assign var=sale_phones value=$report.sale_phones}
  {assign var=sale_lines value=$report.sale_lines}
  {assign var=sale_clients value=$report.sale_clients}
  {assign var=sale_channels value=$report.sale_channels}
  {assign var=del_phones value=$report.del_phones}
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
    <tr>
      <td><b>Всего подано номеров</b></td>
        {foreach from=$regions item=r}
            <td><b>{$sale_phones[$r.id].all}</b></td>
        {/foreach}
        <td><b>{$sale_phones.all.all}</b></td>
    </tr>
    <tr>
      <td>из них новая продажа</td>
        {foreach from=$regions item=r}
          <td>{$sale_phones[$r.id].new}</td>
        {/foreach}
      <td>{$sale_phones.all.new}</td>
    </tr>
    <tr>
      <td>из них допродажа</td>
        {foreach from=$regions item=r}
          <td>{$sale_phones[$r.id].old}</td>
        {/foreach}
      <td>{$sale_phones.all.old}</td>
    </tr>
    <tr>
      <td><b>Всего подано линий без номера</b></td>
        {foreach from=$regions item=r}
          <td><b>{$sale_lines[$r.id].all}</b></td>
        {/foreach}
      <td><b>{$sale_lines.all.all}</b></td>
    </tr>
    <tr>
      <td>из них новая продажа</td>
        {foreach from=$regions item=r}
          <td>{$sale_lines[$r.id].new}</td>
        {/foreach}
      <td>{$sale_lines.all.new}</td>
    </tr>
    <tr>
      <td>из них допродажа</td>
        {foreach from=$regions item=r}
      <td>{$sale_lines[$r.id].old}</td>
        {/foreach}
      <td>{$sale_lines.all.old}</td>
    </tr>
    <tr>
      <td><b>Всего клиентов телефонии</b></td>
        {foreach from=$regions item=r}
          <td><b>{$sale_clients[$r.id].all}</b></td>
        {/foreach}
      <td><b>{$sale_clients.all.all}</b></td>
    </tr>
    <tr>
      <td>из них новых</td>
        {foreach from=$regions item=r}
          <td>{$sale_clients[$r.id].new}</td>
        {/foreach}
      <td>{$sale_clients.all.new}</td>
    </tr>
    <tr>
      <td>из них допродажа</td>
            {foreach from=$regions item=r}
          <td>{$sale_clients[$r.id].old}</td>
        {/foreach}
      <td>{$sale_clients.all.old}</td>
    </tr>
    <tr>
      <td><b>Отключено номеров</b></td>
            {foreach from=$regions item=r}
          <td><b>{$del_phones[$r.id]}</b></td>
        {/foreach}
      <td><b>{$del_phones.all}</b></td>
    </tr>
    <tr>
      <td><b>Отключено линий без номера</b></td>
        {foreach from=$regions item=r}
          <td><b>{$del_lines[$r.id]}</b></td>
        {/foreach}
      <td><b>{$del_lines.all}</b></td>
    </tr>
  </table>
  <br/>
  <table class="price">
    <tr>
      <th>Продажи новых номеров/линий без номера по менеджерам</th>
      <th>шт</th>
      <th>%</th>
    </tr>
    {foreach from=$sale_channels item=sales key=manager}
      {assign var=all value=$sale_phones.all.new+$sale_lines.all.new }
      {assign var=procent value=$sales / $all }
    <tr>
      <td>{$manager|default:"???????"}</td>
      <td><b>{$sales}</b></td>
      <td>{math equation="sales / (phones + lines) * 100" sales=$sales phones=$sale_phones.all.new lines=$sale_lines.all.new format="%d"}%</td>
    </tr>
    {/foreach}
  </table>
{/foreach}
