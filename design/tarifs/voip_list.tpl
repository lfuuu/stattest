<h3>Тарифы IP Телефонии:</h3>
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
<tr><td>Регион:</td><td>
    <select name="f_region" onchange="report()">
        <option value=""> -- Выберите регион -- </option>
    {foreach from=$regions item='r'}
        <option value="{$r.id}"{if $r.id eq $f_region} selected{/if}>{$r.id}. {$r.name}</option>
    {/foreach}
    </select>
</td>
<td rowspan="3" valign="top" align="right" width="300">
    {if access('tarifs','edit')}
    <a href="?module=tarifs&action=voip_edit">Добавить тариф</a>
    {/if}
</td>
</tr>
<tr><td>Направление:</td><td>
    <select name="f_dest" onchange="report()">
        <option value=""> -- Все направления -- </option>
        <option value="4"{if '4' eq $f_dest} selected{/if}>Местные Стационарные</option>
        <option value="5"{if '5' eq $f_dest} selected{/if}>Местные Мобильные</option>
        <option value="1"{if '1' eq $f_dest} selected{/if}>Россия</option>
        <option value="2"{if '2' eq $f_dest} selected{/if}>Международка</option>
        <option value="3"{if '3' eq $f_dest} selected{/if}>СНГ</option>
    </select>
</td></tr>
<tr><td>Валюта:</td><td>
    <select name="f_currency" onchange="report()">
        <option value=""> -- Все валюты -- </option>
        <option value="RUR"{if 'RUR' eq $f_currency} selected{/if}>RUR</option>
        <option value="USD"{if 'USD' eq $f_currency} selected{/if}>USD</option>
    </select>
</td></tr>
<tr><td>Показывать архивные:</td><td>
  <input type="checkbox" name="f_show_archive" {if $f_show_archive > 0}checked{/if} value="1" onchange="report()"/>
</td></tr>
</table>
</form>

<table class=price cellSpacing=2 cellPadding=4 border=0>
    <tr>
        <td class=header rowspan="2">Тариф</td><comment><td class=header rowspan="2">Статус</td><td class=header rowspan="2">Местных минут</td><td class=header rowspan="2">Платная<br/>переадресация</td><td class=header rowspan="2">Мин. платеж</td>
        <td class=header colspan="2"><b>Ежемесячно</b></td><td class=header colspan="2"><b>Подключение</b></td>
        <td class=header rowspan="2">Направление</td><td class=header rowspan="2">Валюта</td><td class=header rowspan="2">Прайслист</td><td class=header rowspan="2">Регион</td></tr>
    <tr><td class=header>за линию</td><td class=header>за номер</td><td class=header>за линию</td><td class=header>за номер</td></tr>
{foreach from=$tarifs_by_dest item='tarifs' key='dest'}
    <tr class="{cycle values='even,odd'}"><td colspan="13"><b>{$dests[$dest]}</b></td></tr>
    {foreach from=$tarifs item='o'}
        <tr class="{cycle values='even,odd'}">
            <td>{if access('tarifs','edit')}<a href='index.php?module=tarifs&action=voip_edit&id={$o.id}'>{$o.name}</a>{else}{$o.name}{/if} {$o.name_short}</td>
            <td>{$o.status}</td><td align="center">{$o.free_local_min}</td><td align="center">{if $o.paid_redirect > 0}да{else}нет{/if}</td><td align="center">{$o.month_min_payment}</td>
            <td align="center">{$o.month_line}</td><td align="center">{$o.month_number}</td><td align="center">{$o.once_line}</td><td align="center">{$o.once_number}</td>
            <td>{$dests[$o.dest]}</td><td align="center">{$o.currency}</td><td>{$pricelists[$o.pricelist_id].name}</td><td>{$regions[$o.region].name}</td></tr>
    {/foreach}
{/foreach}
</table>
