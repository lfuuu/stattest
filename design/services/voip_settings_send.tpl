<h2>Выслать настройки SIP-аккаунта</h2>
{if $isSent}
{if $error}
<h3><b style="color: #c40000;">{$error}</b></h3>
{else}
<h3>Информация выслана</h3>
{/if}
{else}
<div style="width: 500px;float: left;">
<form action="./?module=services&action=vo_settings_send" method=post>
<div style="width: 150px;float: left;">
{html_checkboxes options=$e164s name="e164" separator="<br />"}
</div>

<div  style="width: 250px;float: left;">
{html_checkboxes options=$emails name="email" separator="<br />"}
</div>
<input type="submit" value="Выслать" name="do_send">

</form>
</div>
{if $log}
<div  style="width: 500px;float: left;">
<b>История: </b>
<table>
<tr style="font-size: 8pt;">
<th>Дата</th>
<th>Пользователь</th>
<th>Email</th>
<th>Номера</th>
</tr>
{foreach from=$log item=l}
<tr>
<td>{$l.date}</td>
<td>{$l.user}</td>
<td>{$l.email}</td>
<td>{$l.phones}</td>
</tr>
{/foreach}
</table>
</div>
{/if}
{/if}
