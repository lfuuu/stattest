<h2>Импорт платежей</h2>
<form action="?" method="get">
    <input type="hidden" name="module" value="newaccounts" />
    <input type="hidden" name="action" value="pi_list" />
    Показать за
    <select name="filter[d]">
        {assign var="selected" value=''}
        {if isset($filter.d)}
            {assign var="selected" value=$filter.d}
        {/if}
        <option value=''{if $selected==''} selected{/if}>(любой день месяца)</option>
        <option value='01'{if $selected==1} selected{/if}>01</option>
        <option value='02'{if $selected==2} selected{/if}>02</option>
        <option value='03'{if $selected==3} selected{/if}>03</option>
        <option value='04'{if $selected==4} selected{/if}>04</option>
        <option value='05'{if $selected==5} selected{/if}>05</option>
        <option value='06'{if $selected==6} selected{/if}>06</option>
        <option value='07'{if $selected==7} selected{/if}>07</option>
        <option value='08'{if $selected==8} selected{/if}>08</option>
        <option value='09'{if $selected==9} selected{/if}>09</option>
        <option value='10'{if $selected==10} selected{/if}>10</option>
        <option value='11'{if $selected==11} selected{/if}>11</option>
        <option value='12'{if $selected==12} selected{/if}>12</option>
        <option value='13'{if $selected==13} selected{/if}>13</option>
        <option value='14'{if $selected==14} selected{/if}>14</option>
        <option value='15'{if $selected==15} selected{/if}>15</option>
        <option value='16'{if $selected==16} selected{/if}>16</option>
        <option value='17'{if $selected==17} selected{/if}>17</option>
        <option value='18'{if $selected==18} selected{/if}>18</option>
        <option value='19'{if $selected==19} selected{/if}>19</option>
        <option value='20'{if $selected==20} selected{/if}>20</option>
        <option value='21'{if $selected==21} selected{/if}>21</option>
        <option value='22'{if $selected==22} selected{/if}>22</option>
        <option value='23'{if $selected==23} selected{/if}>23</option>
        <option value='24'{if $selected==24} selected{/if}>24</option>
        <option value='25'{if $selected==25} selected{/if}>25</option>
        <option value='26'{if $selected==26} selected{/if}>26</option>
        <option value='27'{if $selected==27} selected{/if}>27</option>
        <option value='28'{if $selected==28} selected{/if}>28</option>
        <option value='29'{if $selected==29} selected{/if}>29</option>
        <option value='30'{if $selected==30} selected{/if}>30</option>
        <option value='31'{if $selected==31} selected{/if}>31</option>
    </select>

    {assign var="selected" value=''}
    {if isset($filter.m)}
        {assign var="selected" value=$filter.m}
    {/if}
    <select name="filter[m]">
        <option value=''{if $filter.m==''} selected{/if}>(любой месяц)</option>
        <option value='01'{if $selected==1} selected{/if}>янв</option>
        <option value='02'{if $selected==2} selected{/if}>фев</option>
        <option value='03'{if $selected==3} selected{/if}>мар</option>
        <option value='04'{if $selected==4} selected{/if}>апр</option>
        <option value='05'{if $selected==5} selected{/if}>мая</option>
        <option value='06'{if $selected==6} selected{/if}>июн</option>
        <option value='07'{if $selected==7} selected{/if}>июл</option>
        <option value='08'{if $selected==8} selected{/if}>авг</option>
        <option value='09'{if $selected==9} selected{/if}>сен</option>
        <option value='10'{if $selected==10} selected{/if}>окт</option>
        <option value='11'{if $selected==11} selected{/if}>ноя</option>
        <option value='12'{if $selected==12} selected{/if}>дек</option>
    </select>

    {assign var="selected" value=''}
    {if isset($filter.y)}
        {assign var="selected" value=$filter.y}
    {/if}
    <select name="filter[y]">
        <option value=''{if $selected==''} selected{/if}>(любой год)</option>
        {generate_sequence_options_select start=2003 selected=$selected}
    </select>
    <input type="submit" class=button value="Показать" />
</form>
<br />

<table style="text-align: center;border-collapse: collapse;" border="1" colspan="1" cellspacing="0" width="60%">
    <tr>
        <td>/</td>
        {foreach from=$l1 item=c key=k}
            <td colspan={$c.colspan} style="background-color:{if $k=="mcn"}#f5e1e1{elseif $k == "all4net"}#fbfbdd{else}#f0fff0{/if};">
                <b>{$c.title}</b>
            </td>
        {/foreach}
    </tr>
    {foreach from=$payments key=date item=di}
        <tr>
            <td><b>{$date|mdate:"d-m-Y"}</b></td>
            {foreach from=$companyes key=k item=i}
                {foreach from=$i.acc item=a}
                    <td style="padding: 3px 3px 3px 3px;background-color: {if $k=="mcn"}#f5e1e1{elseif $k == "all4net"}#fbfbdd{else}#f0fff0{/if};">
                        {if isset($di[$k][$a]) && $di[$k][$a]}
                            {foreach from=$di[$k][$a].links item=ll key=l}
                                <a href=".?module=newaccounts&action=pi_process&file={$ll}">{if $l == 'all'}{$a} ({$di[$k][$a].count} шт){else}{$l}{/if}</a>
                            {/foreach}
                        {else}
                            &nbsp;
                        {/if}
                        {if $a == "citi" && isset($di[$k].citi_info) && $di[$k].citi_info}
                            <sup title="Дополнительная информация к платежам загруженна" style="color:#20a420; font-size: 7pt;">+info</sup>
                        {/if}
                    </td>
                {/foreach}
            {/foreach}
        </tr>
    {/foreach}
</table>

{*foreach from=$payments item=file name=outer}
<a href='?module=newaccounts&action=pi_process&file={$file}'>{$file}</a><br>
{/foreach*}
<br /><br />
<form enctype="multipart/form-data" action="?" method="post">
    <input type="hidden" name="module" value="newaccounts" />
    <input type="hidden" name="action" value="pi_upload" />
    Выберите файл с платежами: <input class=text name="file" type="file" />
    <input type="submit" class=button value="Загрузить" />
</form>
