<script src="js/ui/jquery-ui.custom.js"></script>
<link rel="stylesheet" href="css/themes/base/jquery-ui.css">
<!--script src="js/ui/jquery.ui.menu.js"></script-->
<style>
  {literal}
  .ui-widget {
    font-size: 1em;
  }
  {/literal}
</style>
<div style="width: 520px; ">

<DIV style="float: left;">
{if (access('clients','read'))}
{if (!isset($clients_my) || !$clients_my) && (!isset($letter) || !$letter) && $module=="clients" && (!isset($client))}
	<span style='color:red;font-weight:bold'>Все клиенты</span> |
{else}
	<a href='{$LINK_START}module=clients&action=all&letter=&region=any'>Все клиенты</a> |
{/if}
</DIV>



<DIV style="float: left; display: none;" id="filter_menu_contener">
  <ul id="filter_menu" STYLE="width: 240px;">
    <li>Фильтр:
        {if isset($letter) && $letter && isset($letters[$letter])}
            {$letters[$letter]}
        {else}
            <span style="color: gray"> нет</span>
        {/if}
    <ul>
{foreach from=$letters key=k item=item}
	  <li><a href='{$LINK_START}module=clients&action=all&region={$letter_region}&letter={$k|escape:'url'}'{if isset($letter) && ($k==$letter)} style='color:red;font-weight:bold'{/if}>{$item}</a></LI>
{/foreach}
      </ul></li>
  </ul>
</DIV>



{if $letter_regions}
<DIV style="float: left; display: none;" id="search_menu_contener">
<ul id="search_menu" STYLE="width: 180px;">
  <li>Регион: {if isset($letter_region) && isset($letter_regions[$letter_region]) && $letter_region != "any"}
      {$letter_regions[$letter_region]}
    {else}
      <span style="color: gray"> ***Любой***</span>
    {/if}

    <ul>
  {foreach from=$letter_regions key=k item=item}
      <li><a href='{$LINK_START}module=clients&action=all&letter={$letter}&region={$k}'{if isset($letter_region) && ($k==$letter_region)} style='color:red;font-weight:bold'{/if}>{$item}</a></li>
  {/foreach}
    </ul></li>
  </ul>
</DIV>
{/if}

  </div>

  {if isset($clients_my) && $clients_my}
	| <span style='color:red;font-weight:bold'>Мои клиенты</span>
{else}
	| <a href='{$LINK_START}module=clients{if isset($client_subj)}&subj={$client_subj}{/if}&action=my'>Мои клиенты</a>
{/if}
<div style="clear: both;"></div>

<FORM action="./?module=clients&action=all" method=get id=searchform name=searchform style='padding: 0,0,0,0; margin:1,1,1,1'>Поиск:
<input type=hidden name=module value=clients><input type=hidden name=action value=all><input type=hidden name=smode value=1>
<input type=text name=search class=text id=searchfield onblur='doHide()' onkeyup="doLoadUp(700)" value='{if isset($search)}{$search}{/if}'>

{if !$view_add_search}
<input type=submit class=button value='Искать'>
{/if}
{if $view_add_search}
<input type=submit class=button value='Искать' onclick='document.getElementById("searchform").smode.value=5; return true;'>
<input type=submit class=button value='по телефону' onclick='document.getElementById("searchform").smode.value=2; return true;'>
<input type=submit class=button value='по voip' onclick='document.getElementById("searchform").smode.value=7; return true;'>
<input type=submit class=button value='IP-адресу' onclick='document.getElementById("searchform").smode.value=3; return true;'>
<input type=submit class=button value='по адресу' onclick='document.getElementById("searchform").smode.value=4; return true;'>
<input type=submit class=button value='по email' onclick='document.getElementById("searchform").smode.value=6; return true;'>
<input type=submit class=button value='по домену' onclick='document.getElementById("searchform").smode.value=8;return true;'>
<input type=submit class=button value='ИНН' onclick='document.getElementById("searchform").smode.value=9; return true;'>
<input type=submit class=button value='Счёт/Заявка' onclick='document.getElementById("searchform").module.value="newaccounts"; document.getElementById("searchform").action.value="search"; return true;'> 
{/if}
<span style="border: 1px solid #ededed; color: #cdcdcd; padding: 2px 2px 2px 2px;" onclick="doAddSearchShow()"> {if !$view_add_search}&gt;&gt;{else}&lt;&lt;{/if} </span>
</FORM>
<div class=text onclick='clearTimeout(timeout2)' style='display: none;position:absolute; margin-left:46px; floating:true; padding:5 5 5 5; width:600px; height:200px' id='variants'></div>
{/if}

<script>
{literal}
function doAddSearchShow()
{
    var s = document.getElementById("additional_search");
    var l = location.href;
    location.href='./?module=clients&action=all&additional_view={/literal}{if $view_add_search}0{else}1{/if}{literal}&retpath='+escape(l);
}

$( "#filter_menu" ).menu({ position: { my: "left top", at: "right-201 top+15" } });
$( "#search_menu" ).menu({ position: { my: "left top", at: "right-130 top+15" } });

$("#filter_menu_contener").show();
$("#search_menu_contener").show();

{/literal}
</script>

