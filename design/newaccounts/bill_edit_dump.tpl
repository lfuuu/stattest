<h2>Бухгалтерия {$fixclient}</h2>
<H3>Редактирование проводки</H3>
<FORM action="?" method=post id=form name=form>
<input type=hidden name=module value=newaccounts>
<input type=hidden name=bill value={$bill.bill_no}>
<input type=hidden name=action value=bill_apply>
Дата проводки: <input type=text name=bill_date value="{$bill.bill_date}">
Валюта проводки: <b style='color:blue'>{$bill.currency}</b><br>
Курьер: {html_options name='courier' options=$l_couriers selected=$bill.courier_id}<br>
Предпологаемый тип платежа: <select name="nal">
<option value="beznal"{if $bill.nal=="beznal"} selected{/if}>безнал</option>
<option value="nal"{if $bill.nal=="nal"} selected{/if}>нал</option>
<option value="prov"{if $bill.nal=="prov"} selected{/if}>пров</option>
</select><br>
<TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr><td width=60%>что</td><td>сколько</td><td>цена</td><td>тип строчки</td><td>&nbsp;</td></tr>
{foreach from=$bill_lines item=item key=key name=outer}<tr>
<td><input class=text type=text value="{$item.item}" name=item[{$key}] style='width:100%'></td>
<td><input class=text type=text value="{$item.amount}" name=amount[{$key}]></td>
<td><input class=text type=text value="{$item.price}" name=price[{$key}]></td>
<td><select name=type[{$key}]>
<option value='service'{if $item.type=='service'} selected{/if}>обычная услуга</option>
<option value='zalog'{if $item.type=='zalog'} selected{/if}>залог (попадает в с/ф-3)</option>
<option value='zadatok'{if $item.type=='zadatok'} selected{/if}>задаток (не попадает в с/ф)</option>
<option value='good'{if $item.type=='good'} selected{/if}>товар</option>
</select></td>
</tr>{/foreach}
</TABLE>
<DIV align=center><INPUT id=submit class=button type=submit value="Изменить"></DIV>
</form>
