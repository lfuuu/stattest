
{foreach from=$i_stages item=t}
<span style="font-size: 8pt;">{$t.date_finish_desired|udate}</span> {if $viewLink}<a href='./?module=tt&action=view&id={$t.trouble_id}'>{$t.state_name}</a>{else}<b>{$t.state_name}</b>{/if} {$t.user_edit}: <u style="background-color: #cfffcf;"> {$t.comment} </u>
        {if $t.doers} {foreach from=$t.doers item=d}<span style="background-color: #cfcfff;">----><b>{$d.depart} {$d.name} </span>({$t.date_start}){if $t.sms} <br><span style="color: #c40000;">{$t.sms.sms_send|udate} // {$t.sms.sms_sender}</span>{/if}</b>{/foreach}{/if}<br>
            {/foreach}