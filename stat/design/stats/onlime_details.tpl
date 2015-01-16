{if !$data}
    <h2>Информация о заказе отстуствует</h2>
{else}
    <h2>Информация о заказе</h2>
    <table class="price">
        {if isset($data.id)}
        <tr>
            <th>
                ID заказа:
            </th>
            <td>
                {$data.id}
            </td>
        </tr>
        {/if}
        {if isset($data.date)}
        <tr>
            <th>
                Дата заказа:
            </th>
            <td>
                {$data.date|mdate:"d месяца Y H:i:s"}
            </td>
        </tr>
        {/if}
        {if isset($data.fio)}
        <tr>
            <th>
                ФИО:
            </th>
            <td>
                {$data.fio}
            </td>
        </tr>
        {/if}
        {if isset($data.phones)}
        <tr>
            <th>
                Телефоны:
            </th>
            <td>
                {foreach from=$data.phones item=p name=phones}
                    {$p}
                    {if !$smarty.foreach.phones.last}
                        , 
                    {/if}
                {/foreach}
            </td>
        </tr>
        {/if}
        {if isset($data.address)}
        <tr>
            <th>
                Адрес доставки:
            </th>
            <td>
                {$data.address}
            </td>
        </tr>
        {/if}
        {if isset($data.delivery)}
        <tr>
            <th>
                Дата доставки:
            </th>
            <td>
                {if isset($data.delivery.date)}
                    {$data.delivery.date|mdate:"d месяца Y"} 
                {/if}
                {if isset($data.delivery.time)}
                    с {$data.delivery.time.from} 
                    до {$data.delivery.time.to}
                {/if}
            </td>
        </tr>
        {/if}
        {if isset($data.comment)}
        <tr>
            <th>
                Коментарий:
            </th>
            <td>
                {$data.comment}
            </td>
        </tr>
        {/if}
    </table>
{/if}
