<ul class="breadcrumb">
    <li>
        <a href="/">Главная</a>
    </li>
    <li>
        <a href="/client/view?id={$account.id}">{$account.name}</a>
    </li>
    <li>
        <a href="/?module=stats&amp;action=ip">Статистика: звонки-IP</a>
    </li>

</ul>
<form action="./?module=stats&action=ip" method="post">
    <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>

        <TBODY>
        <TR>
            <TD class=left>Дата начала отчёта</TD>
            <TD>
                <input class="datepicker-input" type=text name="date_from" value="{$date_from}" id="date_from">
                По:<input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
            </TD>
        </TR>
        <TR>
            <TD class=left>Выводить по:</TD>
            <TD>
                <SELECT name=detality>
                    <OPTION value=call{if $detality=='call'} selected{/if}>звонкам</OPTION>
                    <OPTION value=ip{if $detality=='ip'} selected{/if}>IP</OPTION>
                </SELECT>
            </TD>
        </TR>

        </TBODY>
    </TABLE>
    <HR>

    <DIV align=center><INPUT class=button type=submit name='do' value="Сформировать отчёт"></DIV>
</form>
<script>
  optools.DatePickerInit();
</script>


<H2>Статистика: звонки-IP</H2>
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
    <TBODY>
    <TR>
        {if $detality=='call'}
            <TD class=header vAlign=bottom>#</TD>
            <TD class=header vAlign=bottom>Исходящий номер</TD>
            <TD class=header vAlign=bottom>Входящий номер</TD>
            <TD class=header vAlign=bottom>Дата/время</TD>
            <TD class=header vAlign=bottom>IP</TD>
            <TD class=header vAlign=bottom>Направление</TD>
        {else}
            <TD class=header vAlign=bottom>IP</TD>
            <TD class=header vAlign=bottom>Число звонков</TD>
        {/if}
    </TR>

    {foreach from=$stats item=item key=key name=outer}
        <TR class={if $smarty.foreach.outer.iteration%2==0}even{else}odd{/if}>
            {if $detality=='call'}
                <TD style="color:gray">{$smarty.foreach.outer.iteration}</TD>
                <TD>{$item.number_a}</TD>
                <TD>{$item.number_b}</TD>
                <TD>{$item.date}</TD>
                <TD>{$item.ip}</TD>
                <TD style="color: {if $item.orig}blue;">&darr;&nbsp;входящий{else}
                    green">&uarr;&nbsp;исходящий{/if}</td>
            {else}
                <TD>{$item.ip}</TD>
                <TD>{$item.cnt}</TD>
            {/if}
        </TR>
    {/foreach}
    </TBODY>
</TABLE>
