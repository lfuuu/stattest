{if !isset($hide_tt_list) || $hide_tt_list == 0}
    {if !isset($hide_tts)}
        {if !isset($tt_wo_explain) && $tt_design=='full'}
            <h2>
                {$tt_header}
                {if $fixclient_data}
                    (клиент <a href='{$LINK_START}module=clients&id={$fixclient_data.client}'>{$fixclient_data.client}</a>)
                {/if}
            </h2>
{elseif $tt_design=='client'}
    <h3><a href='{$LINK_START}module=tt&action=list&mode=1'>{$tt_header}</a></h3>
{/if}

{if $tt_design == "full"}
    Найдено {$pager_all} заявок<br>
    {if count($pager_pages)>1}
        Страницы:
        {foreach from=$pager_pages item=i}
            {if $pager_page == $i}
                {$i}
            {else}
                <a href='{$pager_url}&page={$i}&filtred=true'>{$i}</a>
            {/if}
        {/foreach}
        <br>
    {/if}
{else}
    {if $pager_all}
        Показано заявок:
        {if $pager_all > $pager_page_size}
            {$pager_page_size} из {$pager_all}{else} {$pager_all}
        {/if}
    {/if}
{/if}

<table class="table table-condensed table-bordered table-striped table-hover" width="{if $tt_design=='service'}700px{else}100%{/if}">
{if $tt_design == "full"}
    <tr>
        <th>{sort_link sort=1 text='&#8470;' link=$CUR sort_cur=$sort so_cur=$so}</th>
        <th>Дата создания</th>
        <th>{sort_link sort=3 text='Этап' link=$CUR sort_cur=$sort so_cur=$so}</th>
        <th>{sort_link sort=3 text='Ответ.' link=$CUR sort_cur=$sort so_cur=$so}</th>
        <th>Проблема</th>
    </tr>
    <tr style="display: none"><th colspan="5"></th></tr>
    <tr>
        <th nowrap>Тип заявки</th>
        <th>в работе</th>
        <th>{sort_link sort=2 text='Клиент' link=$CUR sort_cur=$sort so_cur=$so}</th>
        <th>Услуга</th>
        <th>Последний коментарий</th>
    </tr>
{/if}

{foreach from=$tt_troubles item=r name=outer}
    <tr style="border-top: 2px solid #ccc; {if $r.is_important}background-color: #f4c0c0;{/if}">
        <td colspan=1><a href='{$LINK_START}module=tt&action=view&id={$r.trouble_id}'><b>{$r.trouble_id}</b></a></td>
        <td colspan=1 nowrap style="font-size:85%;">{mformat param=$r.date_creation format='Y.m.d H:i'}</td>
        <td colspan=1>{$r.state_name}</td>
        <td colspan=1>{$r.user_main}</td>
        <td colspan=1 style="font-size:85%">{$r.problem|replace:"\\r":""|replace:"\\n":" "}</td>
    </tr>

    <tr style="display: none"><td colspan="5"></td></tr>

    <tr style="{if $r.is_important}background-color: #f4c0c0;{/if}">
        <td colspan=1>{$trouble_subtypes_list[$r.trouble_subtype]}</td>
        <td colspan=1 style="font-size:85%;">{$r.time_pass}</td>
        <td colspan=1><a href='{$LINK_START}module=clients&id={$r.client_orig}'>{$r.client}</a></td>
        <td colspan=1 align=center style='font-size:85%;{if !$r.service && $r.bill_no && $r.is_payed == 1}background-color: #ccFFcc;{/if}'>
            {if $r.service}
                <a href='pop_services.php?table={$r.service}&id={$r.service_id}'>{$r.service|replace:"usage_":""}<br>{$r.service_id}</a>
            {elseif $r.bill_no}
                <a href="?module=newaccounts&action=bill_view&bill={$r.bill_no}" style="font-size:100%;font-weight: bold">{$r.bill_no}</a>
            {else}&nbsp;{/if}
        </td>
        <td colspan=1 style="font-size:85%;">{$r.last_comment}</td>
    </tr>

    {if $showStages && $r.state_id!=2}
        <tr {if !isset($bill) || $r.bill_no<>$bill.bill_no}style='display:none'{/if} id='tt_main{$smarty.foreach.outer.iteration}'>
            <td colspan=6>
                <table class={if $tt_design=='service'}insblock{else}price{/if} cellSpacing=4 cellPadding=2 width="100%" border=0>
                    <tr>
                        <td class=header vAlign=bottom width="9%">Состояние</td>
                        <td class=header vAlign=bottom width="8%">Ответственный</td>
                        <td class=header vAlign=bottom width="60%">Комментарий</td>
                        <td class=header vAlign=bottom width="8%">кто</td>
                        <td class=header vAlign=bottom width="15%">когда</td>
                    </tr>
                    {foreach from=$r.stages item=r2 name=inner}
                    <tr class={if $smarty.foreach.inner.iteration%2==count($r.stages)%2}even{else}odd{/if}>
                        <td>{$r2.state_name}</td>
                        <td>{$r2.user_main}</td>
                        <td>{$r2.comment}</td>
                        <td style='font-size:85%'>{$r2.user_edit}</td>
                        <td style='font-size:85%'>{mformat param=$r2.date_edit format='Y.m.d H:i'}</td>
                    </tr>
                    {/foreach}
                </table>
            </td>
        </tr>
    {else}
        <tr style="display: none"><td colspan="5"></td></tr>
    {/if}

    <tr style="display: none"><td colspan="5"></td></tr>
{/foreach}
</table>

{if $tt_design == "full"}
    {if count($pager_pages)>1}
        Страницы:
        {foreach from=$pager_pages item=i}
            {if $pager_page == $i}
                {$i}
            {else}
                <a href='{$pager_url}&page={$i}&filtred=true'>{$i}</a>
            {/if}
        {/foreach}
        <br>
    {/if}
{/if}



{/if}
{/if}
