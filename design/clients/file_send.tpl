<form action="
http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&trunk=5" method=post target=_blank>
<h2>Отослать файл "{$file_name}"</h2>
<table border=0>
<tr><td>На адрес:</td><td>{html_options name=to options=$emails}</td></tr>
</table>
<input type=hidden name=file_content value="{$file_content}">
<input type=hidden name=file_name value="{$file_name_send}">
<input type=hidden name=file_mime value="{$file_mime}">
<input type=hidden name=msg_session value="{$msg_session}">
<input type="submit" name="send_from_stat">
</form>
