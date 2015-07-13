<h2>Тарифы IP Телефонии:
    {if access('tarifs','edit')}
        <a href="?module=tarifs&action=voip_edit">Добавить тариф</a>
    {/if}
</h2>
{literal}
<script type="text/javascript">
    function report(){
        $('#report_form').submit();
    }
</script>
{/literal}
<form id="report_form" method="get">
<input type="hidden" name="module" value="tarifs"/>
<input type="hidden" name="action" value="voip"/>
<table>
    <tr>
        <td>Страна:</td>
        <td>
            <select name="f_country" onchange="report()">
                <option value=""> -- Выберите страну -- </option>
                {foreach from=$countries item='r'}
                    <option value="{$r.code}"{if $r.code eq $f_country} selected{/if}>{$r.name}</option>
                {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td>Регион:</td>
        <td>
            <select name="f_region" onchange="report()">
                <option value=""> -- Выберите регион -- </option>
            {foreach from=$regions item='r'}
                <option value="{$r.id}"{if $r.id eq $f_region} selected{/if}>{$r.id}. {$r.name}</option>
            {/foreach}
            </select>
        </td>
    </tr>
    <tr>
        <td>Валюта:</td>
        <td>
            <select name="f_currency" onchange="report()">
                <option value=""> -- Все валюты -- </option>
                <option value="RUB"{if 'RUB' eq $f_currency} selected{/if}>RUB</option>
                <option value="USD"{if 'USD' eq $f_currency} selected{/if}>USD</option>
                <option value="HUF"{if 'HUF' eq $f_currency} selected{/if}>HUF</option>
                <option value="EUR"{if 'EUR' eq $f_currency} selected{/if}>EUR</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>Показывать архивные:</td>
        <td>
            <input type="checkbox" name="f_show_archive" {if $f_show_archive > 0}checked{/if} value="1" onchange="report()"/>
        </td>
    </tr>
    <tr>
        <td>Направление:</td>
        <td>
            <select name="f_dest" onchange="report()">
                <option value=""> -- Все направления -- </option>
                <option value="4"{if '4' eq $f_dest} selected{/if}>Местные Стационарные</option>
                <option value="5"{if '5' eq $f_dest} selected{/if}>Местные Мобильные</option>
                <option value="1"{if '1' eq $f_dest} selected{/if}>Россия</option>
                <option value="2"{if '2' eq $f_dest} selected{/if}>Международка</option>
            </select>
        </td>
    </tr>
</table>
</form>

<table class="table table-condensed table-striped table-hover">
    <tr>
        <th rowspan="2">Страна</th>
        <th rowspan="2">Точка подключения</th>
        <th rowspan="2">Валюта</th>
        <th rowspan="2">Тариф</th>
        <th rowspan="2">Статус</th>
        <th rowspan="2">Поумолчанию</th>
        <th rowspan="2">Местных минут</th>
        <th rowspan="2">Мин. платеж</th>
        <th rowspan="2">Платная<br/>переадресация</th>
        <th rowspan="2">Метод<br/>тарификации</th>
        <th colspan="2">Ежемесячно</th>
        <th colspan="2">Подключение</th>
        <th rowspan="2">НДС</th>
        <th rowspan="2">Направление</th>
        <th rowspan="2">Прайслист</th>
    </tr>
    <tr>
        <th>за линию</th>
        <th>за номер</th>
        <th>за линию</th>
        <th>за номер</th>
    </tr>
{foreach from=$tarifs_by_dest item='tarifs' key='dest'}
    <tr>
        <td colspan="17"><b>{$dests[$dest]}</b></td>
    </tr>
    {foreach from=$tarifs item='o'}
        <tr>
            <td>{$countries[$o.country_id].name}</td>
            <td>{$regions[$o.region].name}</td>
            <td>{$o.currency}</td>
            <td>{if access('tarifs','edit')}<a href='index.php?module=tarifs&action=voip_edit&id={$o.id}'>{$o.name}</a>{else}{$o.name}{/if} {$o.name_short}</td>
            <td>{$o.status}</td>
            <td align="center">{if $o.is_testing > 0}<b>поумолчанию</b>{else}{/if}</td>
            <td align="center">{$o.free_local_min}</td>
            <td align="center">{$o.month_min_payment}</td>
            <td align="center">{if $o.paid_redirect > 0}да{else}нет{/if}</td>
            <td align="center">
                {if $o.tariffication_free_first_seconds > 0}
                    c 6 секунды,
                {/if}
                {if $o.tariffication_by_minutes > 0}
                    поминутная
                {else}
                    посекундная
                    {if $o.tariffication_full_first_minute > 0}
                        со второй минуты
                    {/if}
                {/if}
            </td>
            <td align="center">{$o.month_line}</td>
            <td align="center">{$o.month_number}</td>
            <td align="center">{$o.once_line}</td>
            <td align="center">{$o.once_number}</td>
            <td align="center">{if $o.price_include_vat}Вкл. НДС{else}Без НДС{/if}</td>
            <td>{$dests[$o.dest]}</td>
            <td>{$pricelists[$o.pricelist_id].name}</td>
        </tr>
    {/foreach}
{/foreach}
</table>
