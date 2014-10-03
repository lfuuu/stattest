    <br><div style="font-size: 8pt;">{if $voip_permit_filtred}<span style="font-size:10pt;">Фильтр по номеру: <b>{$voip_permit_filtred}</b></span><br><a href="./?module=services&action=vo_view">Все регистрации клиента</a>{else}&nbsp;{/if}</div>
    <table width=95% class="price">
        <tr>
            <td class="header">PBX</td>
            <td class="header">Имя</td>
            <td class="header">CallerID</td>
            <td class="header">IP-адрес <br>(инвайт)</td>
            <td class="header">Разрешения</td>
            <td class="header">Зарегестрирован</td>
            <td class="header">Время&nbsp;регегистрации</td>
            <td class="header">UserAgent</td>
            <td class="header">Контакт <br>(инвайта)</td>
            <td class="header">Направление</td>
            {if false && access('services_voip','view_regpass')}
            <td class="header">Пароль</td>
            {/if}
        </tr>
        {foreach from=$voip_permit item=i}

        <tr bgcolor="{if $i.enabled == 't'}{if $i.regvalid == 't'}#DCEEA9{else}#EEDCA9{/if}{else}#a0a0a0{/if}">
            <td>{$i.ippbx}</td>
            <td>{$i.name}</td>
            <td>{$i.callerid}</td>
            <td>{$i.ipaddr}<br>
            {if $i.invite_ip}({$i.invite_ip}){else}&nbsp;{/if}</td>
            <td>{$i.permit}</td>
            <td>{$i.registered}</td>
            <td>{if $i.regtime}{get_time sec=$i.regtime}{/if}</td>
            <td>{$i.useragent}</td>
            <td>{$i.fullcontact}<br>
            {if $i.invite_contact}<br>({$i.invite_contact|wordwrap:45:"<br/>":true}){else}&nbsp;{/if}</td>
            <td>{$i.direction}</td>
            {if false && access('services_voip','view_regpass')}
            <td>{$i.secret|substr:0:3}...{$i.secret|substr:-3}</td>
            {/if}
        </tr>
    {foreachelse}
        <td colspan=8 align=center>Записей не найдено</td>
        {/foreach}
    </table>
