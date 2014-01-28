<!DOCTYPE html>
<html>
<head>
  <base href="{$PATH_TO_ROOT}">
  <TITLE>stat - MCN Телеком</TITLE>
  <meta http-equiv="Content-Type" content="text/html; charset=koi8-r" />
  <META content="&#10;/ MCN | Маркомнет&#10;{ldelim}MCN | Маркомнет{rdelim}&#10;" name=title>
  <META content="Компания MCN. Быстрый интернет по технологии ADSL. IP-телефония, VoIP. Хостинг. Collocation. Подключение под ключ, выезд специалистов, оплата после подключения." name=description>
  <LINK title=default href="main.css" type=text/css rel=stylesheet>
  <script>var LOADED=0;</script>
  <script>var PATH_TO_ROOT="";</script>
  <script src="js/JsHttpRequest.js"></script>
  <script src="js/script.js"></script>
  <script src="js/jquery.js"></script>
  <script src="js/jquery.tmpl.min.js"></script>
  <script src="js/optools.js"></script>
  <script src="js/statlib/main.js"></script>
  <script src="js/jquery.meio.mask.min.js"></script>
  <LINK href="/favicon.ico" rel="SHORTCUT ICON">
</HEAD>
<BODY text=#404040 vLink=#000099 aLink=#000000 link=#000099 bgColor=#efefef>
{if isset($authuser)}
<iframe id=toggle_frame src='?module=usercontrol&action=ex_toggle' height=1 width=1 style='display:none'></iframe>{if access('monitoring','top')}<iframe src='?module=monitoring&action=top' width=100% height=17 style='border:0; padding:0 0 0 0;margin:-15 0 0 0;'></iframe>{/if}
{/if}
<A style="DISPLAY: none" name=top></A><!-- ######## /Header ######## -->
<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>
  <TBODY>
  <TR>
    <TD class=logo-main vAlign=top align=middle width=250><A 
      href="https://lk.mcn.ru/" onclick="this.href=location.protocol+'//'+location.host+location.pathname;"><IMG height=42
      alt="MCN: Internet Service Provider. ISP. ADSL. Hosting. Colocation. IP-телефония" 
      src="{$IMAGES_PATH}logo2.gif" width=113 border=0></A><BR><SPAN 
      class=z10>Сервер статистики</SPAN> 
{if isset($authuser)}<br>{include file="fixclient.tpl"}{/if}
    </TD>
    <TD vAlign=bottom width=15>&nbsp;</TD>
    <TD vAlign=top align=left>
{if isset($top)}{foreach from=$top item=item}{if $item[0] == 0}{$item[1]}{else}{include file="$item[1]"}{/if}{/foreach}{/if}
{if isset($tt_folders_block)}{$tt_folders_block}{/if}
    </TD></TR></TBODY></TABLE><!-- ######## /Logo & User Details ######## -->
<TABLE cellSpacing=0 cellPadding=0 width="100%" border=0>
  <TBODY>
  <TR>
    <TD vAlign=top width=200 style="min-width: 200px"><!-- ######## Left Panel ######## -->{if isset($panel)}{foreach from=$panel item=item}{if $item[0] == 0}{$item[1]}{else}{include file="$item[1]"}{/if}{/foreach}{else}&nbsp;{/if}</TD><!-- ######## /Left Panel ######## -->
    <TD vAlign=bottom width=15>&nbsp;</TD>
    <TD vAlign=top align=left><FONT class=text><div id=div_errors></div>{foreach from=$premain item=item}{if $item[0] == 0}{$item[1]}{else}{include file="$item[1]"}{/if}{/foreach}{foreach from=$main item=item}{if $item[0] == 0}{$item[1]}{else}{include file="$item[1]"}{/if}{/foreach}</FONT></TD>
    <TD vAlign=bottom width=15>&nbsp;</TD></TR></TBODY></TABLE><!-- ######## Footer ######## -->
<DIV style="WIDTH: 1px; HEIGHT: 10px"><IMG height=10 alt="" 
src="{$IMAGES_PATH}1.gif" width=1></DIV>
<DIV style="WIDTH: 1px; HEIGHT: 15px"><IMG height=15 alt="" 
src="{$IMAGES_PATH}1.gif" width=1></DIV>
<DIV style="WIDTH: 1px; HEIGHT: 15px"><IMG height=15 alt=""
src="{$IMAGES_PATH}1.gif" width=1></DIV>
<DIV style="WIDTH: 1px; HEIGHT: 10px"><IMG height=10 alt="" 
src="{$IMAGES_PATH}1.gif" width=1></DIV>
<TABLE style="PADDING-RIGHT: 15px; PADDING-LEFT: 25px" cellSpacing=0 
cellPadding=0 width="100%" border=0>
  <TBODY>
  <TR>
    <TD vAlign=top align=left width="50%"><A href="http://www.mcn.ru/"><IMG 
      height=16 
      alt="MCN: Internet Service Provider. ISP. ADSL. Hosting. Colocation. IP-телефония" 
      src="{$IMAGES_PATH}logo_msn_s.gif" width=58 border=0></A> <BR><FONT 
class=z10 color=#666666>&#0169;2013 MCN. тел. (495) 950&#8211;5678 
(отдел продаж), (495) 950&#8211;5679 (техподдержка)<BR></FONT></TD>
    <TD vAlign=top width="25%">&nbsp;</TD>
    <TD class=z10 vAlign=top align=right width="25%"><A 
      onclick="scrollTo(0,0); return false;" 
      href="https://lk.mcn.ru/" onclick="this.href=location.protocol+'//'+location.host+location.pathname+'#top';">В начало страницы</A>&nbsp;&#8226;<BR><A
      href="http://www.mcn.ru/">На главную страницу</A>&nbsp;&#8226;<BR><A 
      onclick="if (document.all) {ldelim}window.external.AddFavorite(location.href,document.title); return false;{rdelim}" 
      href="https://lk.mcn.ru/">Добавить в
    избранное</A>&nbsp;&#8226;<BR></FONT></TD></TR></TBODY></TABLE>
<DIV style="WIDTH: 1px; HEIGHT: 15px"><IMG height=15 alt="" 
src="{$IMAGES_PATH}1.gif" width=1></DIV>
<script language=JavaScript>LOADED=1;</script>
</BODY></HTML>
