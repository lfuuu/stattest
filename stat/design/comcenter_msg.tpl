<form action="http://thiamis.mcn.ru/welltime/" method=post>
<input type=hidden name=module value="com_agent_panel">
<input type=hidden name=frame value="new_msg">
<input type=hidden name=nav value="mail.none.none">
<input type=hidden name=message value="none">
<input type=hidden name=trunk value="{$mail_trunk_id}">
<input type=hidden name=to value="{$to|escape}">
<input type=hidden name=subject value="{$subject|escape}">
<input type=hidden name=new_msg value="{$new_msg|escape}">
<input type=submit value="{$submit}">
</form>

