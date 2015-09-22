<div class="row" style="overflow-x: auto;">
        {if isset($BIG_ERROR) && $BIG_ERROR}
            <script type='text/javascript'>
                alert('Возникла ошибка. Если у вас проблема, обратитесь к программисту.');
            </script>
        {elseif isset($BIG_VICTORY) && $BIG_VICTORY}
            <script type='text/javascript'>
                alert('Фиксирование успешно завершено.\nGood Luck!');
            </script>
        {/if}

        {if !isset($tt_id)}
            {assign var="tt_id" value=""}
        {/if}

        {if $refix_flag}
        <form method='POST'>
            {/if}
            <table id='timetable_cal_panel_frame' class="col-sm-12"
                   style='position:absolute;visibility:hidden;background-color:silver;border:double; z-index: 10;     width: 200px;'>
                <tr>
                    <td colspan='2'>
                        <div align='right'><a href='#' style='text-decoration:none' id='hide_cal'
                                              onclick='return optools.tt.timetable_event_handler(event);'>X</a></div>
                    </td>
                </tr>
                <tr>
                    <td valign='middle'><a href='#' style='text-decoration:none' id='year_dec'
                                           onclick='return optools.tt.timetable_event_handler(event);'>
                            &lt;&lt;</a><input type='text' size='3' value='2009' readonly='readonly'
                                               id='timetable_cal_year_area'/><a href='#' style='text-decoration:none'
                                                                                id='year_inc'
                                                                                onclick='return optools.tt.timetable_event_handler(event);'>
                            &gt;&gt;</a></td>
                    <td valign='middle'><a href='#' style='text-decoration:none' id='month_dec'
                                           onclick='return optools.tt.timetable_event_handler(event);'>
                            &lt;&lt;</a><input type='text' size='3' value='Авг' readonly='readonly'
                                               id='timetable_cal_month_area'/><a href='#' style='text-decoration:none'
                                                                                 id='month_inc'
                                                                                 onclick='return optools.tt.timetable_event_handler(event);'>
                            &gt;&gt;</a></td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <table id='timetable_cal_panel' align='center' border='0' cellpadding='1'
                               cellspacing='1'></table>
                    </td>
                </tr>
            </table>
            {if $refix_flag}
        </form>
        {/if}

        {if $refix_flag}
        <form method='POST'>
            <input type='hidden' name='module' value='tt'/>
            <input type='hidden' name='action' value='refix_doers'/>
            <input type='hidden' name='fix' value='ok'/>
            <input type='hidden' name='id' value='{$tt_id}'/>
            {/if}
            <table class='timetable col-sm-12' cellspacing=0 border=0 id='timetable_pane' align='center'
                   {if !isset($timetableShow)}style='display:none'{/if}>
                <tr>
                    <td colspan='48'>
                        <div align='right' style='background-color:lightgray'><a href='#' style='text-decoration:none'
                                                                                 id='show_cal'
                                                                                 onclick='return optools.tt.timetable_event_handler(event);'>Другая
                                дата</a></div>
                    </td>
                </tr>
                <tr>
                    <td>Исполнитель</td>
                    <td colspan='15'>{$dates.yesterday}</td>
                    <td>&nbsp;</td>
                    <td colspan='15'>{$dates.today}</td>
                    <td>&nbsp;</td>
                    <td colspan='15'>{$dates.tomorrow}</td>
                </tr>
                <tr>
                    <td align='right'>&nbsp;</td>
                    {section loop=24 start=9 name='myloop'}
                        <td>{$smarty.section.myloop.index}</td>
                    {/section}
                    <td>&nbsp;</td>
                    {section loop=24 start=9 name='myloop'}
                        <td>{$smarty.section.myloop.index}</td>
                    {/section}
                    <td>&nbsp;</td>
                    {section loop=24 start=9 name='myloop'}
                        <td>{$smarty.section.myloop.index}</td>
                    {/section}
                </tr>
                {foreach from=$tt_doers item='doers_d' key='depart'}
                <tr align='center'>
                    <td colspan='48'>{$depart}</td>
                </tr>
                {foreach from=$doers_d item='doer' key='doer_id'}
                <tr>
                    <td>{$doer.name}</td>
                    {section loop=24 start=9 name='myloop'}
                    <td style="{if isset($doer.time[$dates.key.yesterday].here) && $doer.time[$dates.key.yesterday].here eq $smarty.section.myloop.index}font-weight:bold;text-decoration:blink;{/if}">
                        {if isset($doer.time[$dates.key.yesterday][$smarty.section.myloop.index]) && $doer.time[$dates.key.yesterday][$smarty.section.myloop.index]}
                        {if $refix_flag eq true && $doer.time[$dates.key.yesterday].here eq $smarty.section.myloop.index}
                    <input type='checkbox' checked='checked'
                           name='doer_fix[{$dates.key.yesterday}][{$smarty.section.myloop.index}][{$doer_id}]'
                           value='1' {*onclick='optools.tt.ctl_chckbxs(this)'*} />
                        {*<script type='text/javascript'>
                            optools.tt.registerAutoClick('doer_fix[{$dates.key.yesterday}][{$smarty.section.myloop.index}][{$doer_id}]');
                        </script>*}
                        {else}
                        {foreach from=$doer.time[$dates.key.yesterday][$smarty.section.myloop.index] item=d name="f_yesterday"}
                        <a style="color:{
                                if
                                $d.state eq 2
                                }green{
                                elseif
                                $d.state eq 4
                                }red{
                                else
                                }blue{/if};text-decoration:none;"
                           href="?module=tt&action=view&id={$d.trouble}">{$d.client}</a>&nbsp;{if $d.bill_no}<br><a
                        href="./?module=newaccounts&action=bill_view&bill={$d.bill_no}">{$d.bill_no}</a>{/if}
                        {if $d.state_name}<br>{$d.state_name}{/if} {if $d.is_wrong}{$d.is_wrong}{/if}
                        {if $smarty.foreach.f_yesterday.total > 1 && !$smarty.foreach.f_yesterday.last}
                            <hr>
                        {/if}
                        {/foreach}

                        {/if}
                        {else}
                        &nbsp;
                        {/if}
                    </td>
                    {/section}
                    <td>&nbsp;</td>
                    {section loop=24 start=9 name='myloop'}
                    <td style="{if isset($doer.time[$dates.key.today].here) && $doer.time[$dates.key.today].here eq $smarty.section.myloop.index}font-weight:bold;text-decoration:blink;{/if}">
                        {if isset($doer.time[$dates.key.today][$smarty.section.myloop.index]) && $doer.time[$dates.key.today][$smarty.section.myloop.index]}
                        {if $refix_flag eq true && $doer.time[$dates.key.today].here eq $smarty.section.myloop.index}
                    <input type='checkbox' checked='checked'
                           name='doer_fix[{$dates.key.today}][{$smarty.section.myloop.index}][{$doer_id}]'
                           value='1' {*onclick='optools.tt.ctl_chckbxs(this)'*} />
                        {*<script type='text/javascript'>
                            optools.tt.registerAutoClick('doer_fix[{$dates.key.today}][{$smarty.section.myloop.index}][{$doer_id}]');
                        </script>*}
                        {else}
                        {foreach from=$doer.time[$dates.key.today][$smarty.section.myloop.index] item=d name="f_today"}
                        <a style="color:{
                                if
                                $d.state eq 2
                                }green{
                                elseif
                                $d.state eq 4
                                }red{
                                else
                                }blue{/if};text-decoration:none;" target="_blank"
                           href="?module=tt&action=view&id={$d.trouble}">{$d.client}</a>&nbsp;{if $d.bill_no}<br><a
                        href="./?module=newaccounts&action=bill_view&bill={$d.bill_no}">{$d.bill_no}</a>{/if}
                        {if $d.state_name}<br>{$d.state_name}{/if} {if $d.is_wrong}{$d.is_wrong}{/if}
                        {if $smarty.foreach.f_today.total > 1 && !$smarty.foreach.f_today.last}
                            <hr>
                        {/if}
                        {/foreach}
                        {/if}
                        {elseif (!$flag_chck || $refix_flag) && $tt_id}
                        <input type='checkbox'
                               name='doer_fix[{$dates.key.today}][{$smarty.section.myloop.index}][{$doer_id}]'
                               value='1' {*onclick='optools.tt.ctl_chckbxs(this)'*} />
                        {else}
                        &nbsp;
                        {/if}
                    </td>
                    {/section}
                    <td>&nbsp;</td>
                    {section loop=24 start=9 name='myloop'}
                        <td style="{if isset($doer.time[$dates.key.tomorrow].here) && $doer.time[$dates.key.tomorrow].here eq $smarty.section.myloop.index}font-weight:bold;text-decoration:blink;{/if}">
                            {if isset($doer.time[$dates.key.tomorrow][$smarty.section.myloop.index]) && $doer.time[$dates.key.tomorrow][$smarty.section.myloop.index]}
                                {if $refix_flag eq true && $doer.time[$dates.key.tomorrow].here eq $smarty.section.myloop.index}
                                    <input type='checkbox' checked='checked'
                                           name='doer_fix[{$dates.key.tomorrow}][{$smarty.section.myloop.index}][{$doer_id}]'
                                           value='1' {*onclick='optools.tt.ctl_chckbxs(this)'*} />
                                    {*<script type='text/javascript'>
                                        optools.tt.registerAutoClick('doer_fix[{$dates.key.tomorrow}][{$smarty.section.myloop.index}][{$doer_id}]');
                                    </script>*}
                                {else}
                                    {foreach from=$doer.time[$dates.key.tomorrow][$smarty.section.myloop.index] item=d name="f_tomorrow"}
                                    <a style="color:{
								if
									$d.state eq 2
                                        }green{
								elseif
									$d.state eq 4
                                        }red{
								else
                                        }blue{/if};text-decoration:none;" target="_blank"
                                                                          href="?module=tt&action=view&id={$d.trouble}">{$d.client}</a>&nbsp;{if $d.bill_no}
                                <br>
                                <a href="./?module=newaccounts&action=bill_view&bill={$d.bill_no}">{$d.bill_no}</a>{/if}
                                {if $d.state_name}<br>{$d.state_name}{/if} {if $d.is_wrong}{$d.is_wrong}{/if}
                                {if $smarty.foreach.f_tomorrow.total > 1 && !$smarty.foreach.f_tomorrow.last}
                                    <hr>
                                {/if}
                            {/foreach}
                            {/if}
                            {elseif (!$flag_chck || $refix_flag) && $tt_id}
                            <input type='checkbox'
                                   name='doer_fix[{$dates.key.tomorrow}][{$smarty.section.myloop.index}][{$doer_id}]'
                                   value='1' {*onclick='optools.tt.ctl_chckbxs(this)'*} />
                            {else}
                            &nbsp;
                            {/if}
                        </td>
                    {/section}
                </tr>
                {/foreach}
                {/foreach}
                <tr>
                    <td style="background-color:lightblue" colspan='48' align='center'>
                        {if $refix_flag}
                            <input type='submit' value='Fix'/>
                        {elseif $tt_id}
                            <a href="?module=tt&action=refix_doers&tt_id={$tt_id}" target="_blank">Ошибка?</a>
                        {/if}
                    </td>
                </tr>
            </table>
            <script type='text/javascript'>
                optools.tt.tt_autoClick();
            </script>
            {if $refix_flag}
        </form>
        {/if}
</div>