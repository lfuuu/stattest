<table class=insblock cellspacing=4 cellpadding=2 border=0>
<tr><th>имя файла</th><th>комментарий</th><th>кто</th><th>когда&nbsp;&nbsp;&nbsp;&nbsp;</th><th>&nbsp;</th></tr>
{foreach from=$files item=item}<tr>
	<td><a href='{$LINK_START}module=clients&id={$item->id}&action=file_get&cid={$item->client_id}'>{$item.name}</a> 
    <a href="/client/view?id={$item.id}&action=file_send&cid={$item.client_id}"><img border=0 src='images/icons/envelope.gif'></a></td>
	<td>{$item->comment}</td>
	<td>{$item->user->name}</td>
	<td style='font-size:85%'>{$item->ts|udate:'Y-m-d H:i:s'}</td>
	<td>
		<a href='{$LINK_START}module=clients&id={$item->client_id}&action=file_del&file_id={$item->id}' onclick="return confirm('Вы уверены, что хотите удалить файл {$item->name}?')"><img style='margin-left:-2px;margin-top:-3px' class=icon src='{$IMAGES_PATH}icons/delete.gif' alt="Удалить"></a>
	</td>
</tr>{/foreach}
<FORM action="/" method=post enctype="multipart/form-data"><tr>
	<input type=hidden name=module value=clients>
	<input type=hidden name=action value=file_put>
	<input type=hidden name=cid value={$client_id}>
	<td><input class=text style='width:100' type=text name=name value=""></td>
	<td><input class=text style='width:200' type=text name=comment></td>
	<td><input type=file name=file></td>
	<td colspan=2><input class=button type=submit value="загрузить"></td>
</tr></form>
</table>
