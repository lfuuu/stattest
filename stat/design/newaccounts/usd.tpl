<H2>Курс доллара</H2>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=action value=usd>
<input type=hidden name=module value=newaccounts>
Дата: <input type=text name=date value='{$cur_date}'>
Курс: <input type=text name=rate value=''>
<INPUT id=submit class=button type=submit value="Изменить">
</form>
<table class=price><tr><td>Дата</td><td>Курс</td></tr>
{foreach from=$rates item=item}
<tr><td>{$item.date}</td><td>{$item.rate}</td></tr>
{/foreach}
</table>
