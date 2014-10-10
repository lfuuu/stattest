<DIV class=leftpanel style='padding-bottom:3px;padding-top:3px;margin-top:0px;margin-bottom:0px'>
<B><a href="{$LINK_START}module={$panel_module}">{$panel_title}</a> <a href='javascript:toggle(document.getElementById("panel_id{$panel_id}"),link{$panel_id},"{*if $module!=$panel_module*}{$panel_module}{*/if*}");' id='link{$panel_id}'>{if $module==$panel_module || (isset($authuser.data_panel.$panel_module) && $authuser.data_panel.$panel_module==1)}&laquo;{else}&raquo;{/if}</a></B><br>
<span id='panel_id{$panel_id}' style='display:{if $module==$panel_module || (isset($authuser.data_panel.$panel_module) && $authuser.data_panel.$panel_module==1)}inline{else}none{/if}'><br style='font-size:3px'>
{foreach from=$panel_data item=item name=outer}
{if (isset($item[0]) && $item[0])}
<IMG height=17 alt="" hspace=2 src="{$IMAGES_PATH}{if (isset($item[2]) && $item[2])}{$item[2]}{else}1{/if}.gif" width=17 align=absMiddle>
<A href="{$LINK_START}{$item[1]}">{$item[0]}</A>{if isset($item[3])} {$item[3]}{/if}<BR>
{else}
<BR>
{/if}
{/foreach}<br><br></span>
</DIV>
