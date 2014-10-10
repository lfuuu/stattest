<H2>Услуги</H2>
<H3>Интернет подключения</H3>
<a href='{$LINK_START}module=services&action=in_report'>Отчет по подключениям</a><br>
<br><br><br><hr><br>
<a href='{$LINK_START}module=services&action=in_view_ind'>Индивидуальные подключения</a><br>

<form style='padding:0 0 0 0; margin:10px 0 0 0' action='?' method=get>
<input type=hidden name=module value='services'>
<input type=hidden name=action value='in_view_routed'>
Подключения по роутеру <select class=text name=router>{foreach from=$serv_routers item=item}<option value={$item}>{$item}</option>{/foreach}</select><input class=text type=submit value='Посмотреть'></form><br>

<a href='{$LINK_START}module=services&action=in_view_bad'>Подключения с ошибками</a><br>
<a href='{$LINK_START}module=services&action=in_view_noport'>Подключения с непрописанными портами</a><br>
