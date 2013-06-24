{if $res == 'ok'}
Платеж прошел успешно
{elseif $res == 'err'}
Платеж НЕ прошел
{elseif $pay_type == 'card'}
<FORM ACTION="https://test.wpay.uniteller.ru/pay/" METHOD="POST">
Пополнение баланса на {$pay_sum} рублей банковской картой<br/>
<INPUT TYPE="HIDDEN" NAME="Shop_IDP" VALUE="{$shop_id}">
<INPUT TYPE="HIDDEN" NAME="Order_IDP" VALUE="{$order_id}">
<INPUT TYPE="HIDDEN" NAME="Subtotal_P" VALUE="{$pay_sum}">
<INPUT TYPE="HIDDEN" NAME="Signature" VALUE="{$signature}">
<INPUT TYPE="SUBMIT" NAME="Submit" VALUE="Перейти на сайт банка для оплаты">
<INPUT TYPE="HIDDEN" NAME="URL_RETURN_OK" VALUE="{$back_ok}">
<INPUT TYPE="HIDDEN" NAME="URL_RETURN_NO" VALUE="{$back_err}">
</FORM>
{else}
Номер карты - 4405050300000000<br/>Срок действия - 12/2015<br/>CVC2- 123<br/>
<form method="post">
<input type=hidden name="module" value="clientaccounts">
<input type=hidden name="action" value="pay">
Введите сумму пополнения:
<input type=text name="sum" value="100"><br/>
<input type=hidden name="pay_card" value="1">
<input type=submit value="Пополнить">
</form>
{/if}