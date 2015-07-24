<table align='center'>
    <tr style='background-color:lightgreen;text-align:center'><td>Номер</td><td>{$e164}</td></tr>
    <tr style='background-color:lightgreen;text-align:center'><td>Характеристика</td><td>
        <table>

            <tr style='background-color:lightblue;text-align:right'><td style='font-weight:bold'>{if $is_using}Используется{else}Свободен{/if}</td></tr>
            <tr style='background-color:lightblue;text-align:right'><td style='font-weight:bold'>{if $client_id==764}Наш номер{else}Арендуемый{/if}</td></tr>
            {if $beauty_level == 0}
                <tr style='background-color:lightblue;text-align:right'><td><b>Не красивый</b></td></tr>
            {elseif $beauty_level == 4}
                <tr style='background-color:lightblue;text-align:right'><td><b>Красивый</b> (Бронзовый)</td></tr>
            {elseif $beauty_level == 3}
                <tr style='background-color:lightblue;text-align:right'><td><b>Красивый</b> (Серебряный)</td></tr>
            {elseif $beauty_level == 2}
                <tr style='background-color:lightblue;text-align:right'><td><b>Красивый</b> (Золотой)</td></tr>
            {elseif $beauty_level == 1}
                <tr style='background-color:lightblue;text-align:right'><td><b>Красивый</b> (Платиновый)</td></tr>
            {/if}
            {if $is_using == false and $is_reserved}
            <tr style='background-color:lightblue;text-align:right'><td style='font-weight:bold'>Зарезервирован</td></tr>
            {/if}
            {if $count_calls}
            <tr style='background-color:lightblue;text-align:right'><td style='font-weight:bold'>Звонков за 2 дня: {$count_calls}</td></tr>
            {/if}
            {if $client_id and ($is_using or $client_id != 764)}
                <tr style='background-color:lightblue;text-align:right'><td>Клиент: <a href='/client/view?id={$client_id}' target='_blank'>{$client} {$company}</a></td></tr>
            {/if}
        </table>
    </td></tr>
    <tr style='background-color:lightgreen'><td style='text-align:center'>Действия</td><td>

    Дейсвтия не доступны
    {if false}
        <ul>
            {if $is_using == false}
                {if $client_id==764}
                    <li><a href='?module=services&action=e164_edit&e164={$e164}&reserve=0'>Сделать арендуемым</a></li>
                {else}
                    {if $is_reserved}
                        <!-- li><a href='?module=services&action=e164_edit&e164={$e164}&reserve=0'>Снять резерв</a></li -->
                    {elseif $current_client_id}
                        <li><a href='?module=services&action=e164_edit&e164={$e164}&reserve={$current_client_id}'>Зарезервировать за клиентом {$current_client}</a></li>
                        <li><a href='?module=services&action=e164_edit&e164={$e164}&reserve=764'>Сделать своим</a></li>
                    {/if}
                {/if}
            {/if}
        </ul>
    {/if}
    </td></tr>
</table>

{if count($logs)>0}
<table align='center'>
    <tr><td colspan='2' style='text-align:center'>Операции с номером</td></tr>
    {foreach from=$logs item='record'}
        <tr><td style='text-align:center;font-weight:bolder;color:#555'>{$record.human_time}</td><td>
        {if $record.action eq 'fix'}
            <a style='text-decoration:none;font-weight:bold' href='?module=employeers&user={$record.user}'>{$record.user}</a> <b>зафиксирован</b> за клиентом <a style='text-decoration:none;font-weight:bold' href='/client/view?id={$record.client_id}'>{$record.client}</a>
        {elseif $record.action eq 'unfix'}
            <a style='text-decoration:none;font-weight:bold' href='?module=employeers&user={$record.user}'>{$record.user}</a> <b>снят</b> с клиента <a href='/client/view?id={$record.client_id}' style='text-decoration:none;font-weight:bold'>{$record.client}</a>
        {elseif $record.action eq 'invertReserved' and $record.client_id == 764}
            <a style='text-decoration:none;font-weight:bold' href='?module=employeers&user={$record.user}'>{$record.user}</a> {if $record.addition eq 'Y'}<b>Сделан Своим</b>{else}<b>Сделан Арендуемым</b>{/if}
        {elseif $record.action eq 'invertReserved'}
            <a style='text-decoration:none;font-weight:bold' href='?module=employeers&user={$record.user}'>{$record.user}</a> {if $record.addition eq 'Y'}<b>Зарезервирован</b> за клиентом{else}<b>Снят резерв</b> с клиента{/if} <a href='/client/view?id={$record.client_id}' style='text-decoration:none;font-weight:bold'>{$record.client}</a>
        {/if}
        </td></tr>
    {/foreach}
</table>
{/if}
