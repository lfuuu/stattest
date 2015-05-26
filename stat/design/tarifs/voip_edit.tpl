<h3>Тарифы IP Телефонии (редактирование):</h3>
<form method="post">
<input type="hidden" name="module" value="tarifs" />
<input type="hidden" name="action" value="voip_edit" />
<input type="hidden" name="id" value="{$data.id}" />
<table>
    <tr><td>Страна:</td><td>
            {if $data.id > 0}
                <input type="hidden" name="country_id" value="{$data.country_id}" />
                {$countries[$data.country_id].name}
            {else}
                <select name="country_id">
                    {foreach from=$countries item='r'}
                        <option value="{$r.code}"{if $r.code eq $data.country_id} selected{/if}>{$r.name}</option>
                    {/foreach}
                </select>
            {/if}
        </td></tr>
    <tr><td>Регион:</td><td>
    {if $data.id > 0}
        <input type="hidden" name="region" value="{$data.region}" />
        {$regions[$data.region].name}
    {else}
        <select name="region">
            {foreach from=$regions item='r'}
                <option value="{$r.id}"{if $r.id eq $data.region} selected{/if}>{$r.id}. {$r.name}</option>
            {/foreach}
        </select>
    {/if}
    </td></tr>
    <tr><td>Направление:</td><td>
    {if $data.id > 0}
        {$dests[$data.dest]}
    {else}
        <select name="dest">
            <option value="4"{if '4' eq $data.dest} selected{/if}>Местные Стационарные</option>
            <option value="5"{if '5' eq $data.dest} selected{/if}>Местные Мобильные</option>
            <option value="1"{if '1' eq $data.dest} selected{/if}>Россия</option>
            <option value="2"{if '2' eq $data.dest} selected{/if}>Международка</option>
            <option value="3"{if '3' eq $data.dest} selected{/if}>СНГ</option>
        </select>
    {/if}
    </td></tr>
    <tr><td>Валюта:</td><td>
    {if $data.id > 0}
        {$data.currency}
    {else}
        <select name="currency">
            <option value="RUB"{if 'RUB' eq $data.currency} selected{/if}>RUB</option>
            <option value="USD"{if 'USD' eq $data.currency} selected{/if}>USD</option>
            <option value="HUF"{if 'HUF' eq $data.currency} selected{/if}>HUF</option>
            <option value="EUR"{if 'EUR' eq $data.currency} selected{/if}>EUR</option>
        </select>
    {/if}
    </td></tr>
    <tr><td>Состояние:</td><td>
        <select name="status">
            <option value='public'{if $data.status eq 'public'} selected{/if}>публичный</option>
            <option value='special'{if $data.status eq 'special'} selected{/if}>специальный</option>
            <option value='archive'{if $data.status eq 'archive'} selected{/if}>архивный</option>
            <option value='operator'{if $data.status eq 'operator'} selected{/if}>оператор</option>
        </select>
    </td></tr>
    <tr><td>Название тарифа:</td><td>
        <input type="text" name="name" value="{$data.name}"/>
    </td></tr>
    <tr><td>Короткое название:</td><td>
        <input type="text" name="name_short" value="{$data.name_short}"/>
    </td></tr>
    <tr><td>ежемесячная плата за линию:</td><td>
        <input type="text" name="month_line" value="{$data.month_line}"/>
    </td></tr>
    <tr><td>ежемесячная плата за номер:</td><td>
        <input type="text" name="month_number" value="{$data.month_number}"/>
    </td></tr>
    <tr><td>минимальный платеж:</td><td>
        <input type="text" name="month_min_payment" value="{$data.month_min_payment}"/>
    </td></tr>
    <tr><td>плата за подключение линии:</td><td>
        <input type="text" name="once_line" value="{$data.once_line}"/>
    </td></tr>
    <tr><td>плата за подключение номера:</td><td>
        <input type="text" name="once_number" value="{$data.once_number}"/>
    </td></tr>
    <tr><td>бесплатных местных минут:</td><td>
        <input type="text" name="free_local_min" value="{$data.free_local_min}"/>
    </td></tr>
    <tr><td></td><td><label>
                <input type="checkbox" name="freemin_for_number" value="1" {if $data.freemin_for_number > 0}checked{/if} />
                бесплатные минуты для номера (да) или для линии (нет):</label>
    </td></tr>
    <tr><td></td><td><label>
                <input type="checkbox" name="paid_redirect" value="1" {if $data.paid_redirect > 0}checked{/if} />
                платные переадресации
            </label>
    </td></tr>
    <tr><td></td><td><label>
                <input type="checkbox" name="tariffication_by_minutes" value="1" {if $data.tariffication_by_minutes > 0}checked{/if} />
                тарификация: поминутная (да), посекундная (нет)</label>
        </td></tr>
    <tr><td></td><td><label>
                <input type="checkbox" name="tariffication_full_first_minute" value="1" {if $data.tariffication_full_first_minute > 0}checked{/if} />
                тарификация: первая минута оплачивается полностью</label>
        </td></tr>
    <tr><td></td><td><label>
                <input type="checkbox" name="tariffication_free_first_seconds" value="1" {if $data.tariffication_free_first_seconds > 0}checked{/if} />
                тарификация: первые 5 секунд бесплатно</label>
        </td></tr>
    <tr><td></td><td><label>
                <input type="checkbox" name="is_virtual" value="1" {if $data.is_virtual > 0}checked{/if} />
                тариф для виртуальных номеров</label>
        </td></tr>
    <tr><td></td><td><label>
                <input type="checkbox" name="is_testing" value="1" {if $data.is_testing > 0}checked{/if} />
                тариф поумолчанию</label>
        </td></tr>
    <tr><td>прайс-лист:</td><td>
        <select class="select2" name="pricelist_id" style="width: 400px">
        {foreach from=$pricelists item='r'}
            <option value="{$r.id}"{if $r.id eq $data.pricelist_id} selected{/if}>{$r.name}</option>
        {/foreach}
        </select>
    </td></tr>
    <tr><td>пользователь, изменивший тариф последний раз:</td><td>
        {$data.user}
    </td></tr>
    <tr><td>время последнего изменения тарифа:</td><td>
        {$data.edit_time|udate}
    </td></tr>
</table>
<input type="submit" value="Сохранить"/>
</form>

