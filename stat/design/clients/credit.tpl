сумма кредита, вписанная в поле = сумма по 3м последним счетам; автоматически устанавливается утроенная сумма тарифа.
<b>клиент не задан</b><br>
все параметры рассчитываются автоматически<br>
<br>
<b>клиент задан</b><br>
услуга не задана, сумма не задана => по всем услугам данного клиента сумма рассчитывается автоматически<br>
услуга не задана, сумма задана => по всем услугам данного клиента устанавливается заданная сумма<br>
услуга задана, сумма не задана => по данной услуге данного клиента автоматически рассчитывается сумма<br>
услуга задана, сумма задана => по данной услуге данного клиента устанавливается заданная сумма<br>
<FORM action="?" method=get>
<input type=hidden name=module value=clients>
<input type=hidden name=action value=credit>
<input type=hidden name=process value=1>
Клиент: <input type=text class=text name=client value='{$fixclient}'><br>
Сумма кредита: <input type=text class=text name=sum value='{$credit_sum}'><br>
Услуга: <select class=text name=service>{foreach from=$services item=item name=outer}<option value="{$item}"{if $item==$service} selected{/if}>{if !$item}-все услуги-{else}{$item}{/if}</option>{/foreach}</select><br>
<br><br>
<input type=submit class=text value='Установить'>
</form>
