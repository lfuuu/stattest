<H2>Виртуальная АТС</H2>
<H3>Аварийная переадресация</H3>
В случае, если абонентское устройство по каким-либо причинам станет недоступно, звонки будут направляться на выбранный Вами номер.<br>
<FORM action="?" method=get id=form name=form>
<input type=hidden name=action value=readdr_failure>	
<input type=hidden name=module value=phone>
Номер телефона: <input name=phone class=text value='{if isset($phone_readdr.phone)}{$phone_readdr.phone}{/if}'><br>
<INPUT id=submit class=button type=submit value="Изменить"><br>
</form>
