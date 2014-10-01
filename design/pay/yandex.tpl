{* http://money.yandex.ru/doc.xml?id=459801#1 *}
{if $paymode=='demo'}
<form method="POST" action="http://demomoney.yandex.ru/select-wallet.xml"> {*отправляет пользователя на страницу выбора кошелька *}
<input type="hidden" name="TargetCurrency" value="10643"> {*код валюты. 643=рубли*}
<input type="hidden" name="currency" value="10643">{*код валюты. 643=рубли*}
<input type="hidden" name="BankID" value="1003"> {*идентификатор процессингового центра платежной системы = 100 *}
<input type="hidden" name="TargetBankID" value="1003"> {*идентификатор процессингового центра платежной системы = 1001 *}
<input type="hidden" name="ShopID" value="65535"> {*идентификатор магазина в ЦПП - уникальное значение, присваивается Магазину платежной системой*}
{elseif $paymode=='real'}
<form method="POST" action="http://money.yandex.ru/select-wallet.xml"> {*отправляет пользователя на страницу выбора кошелька *}
<input type="hidden" name="TargetCurrency" value="643"> {*код валюты. 643=рубли*}
<input type="hidden" name="currency" value="643">{*код валюты. 643=рубли*}
<input type="hidden" name="BankID" value="1001"> {*идентификатор процессингового центра платежной системы = 100 *}
<input type="hidden" name="TargetBankID" value="1001"> {*идентификатор процессингового центра платежной системы = 1001 *}
<input type="hidden" name="ShopID" value="65535"> {*идентификатор магазина в ЦПП - уникальное значение, присваивается Магазину платежной системой*}
{/if}
<input type="hidden" name="wbp_InactivityPeriod" value="2">
<input type="hidden" name="wbp_ShopAddress" value="wn1.paycash.ru:8828">
<input type="hidden" name="wbp_ShopEncryptionKey" value="hAAAEiBcBAF2bL+RJ6gP15A3iqG+/H3+RyfkraP3O0+BdvQ7NehQAjgCZe6d4vAtLk8v+598PNLgvt6Q1z1nCYXZuvqhJ6GEonn8P2ADrl+rlOmTNmFYWs5zP5pYUMEOmmkIK17KOuOenHprS5SdU+UHsnO4JqMgqfxV1H6L4eVpi1ZLWKImH">
<input type="hidden" name="wbp_ShopKeyID" value="4060341895">
<input type="hidden" name="wbp_Version" value="1.0">
<input type="hidden" name="wbp_CorrespondentID" value="8993748E663DE6B3C68D2D9931B079C74789D4B4">
<input type="hidden" name="PaymentTypeCD" value="PC"> {*тип платежа: по технологии PayCash *}

<table>
<tr><td>Номер заказа:</td><td>{CustomerNumber}
{$fixclient_data.id} ({$fixclient_data.company_full}, {$fixclient_data.client})
<input type=hidden name="CustomerNumber" size="20" value="{$fixclient_data.id}">
{* ключевая информация платежа: номер заказа в интернет-магазине, лицевой счет у провайдера услуг (если данное поле не заполнено платеж в ЦПП не пройдет)  *}
</td></tr><tr><td>Сумма:</td><td>
<input type=text name="Sum" size="10" value="300"> рублей
{* сумма оплаты (ЦПП формирует и передает контракт в кошелек клиента именно на эту сумму) *}
</td></tr><tr><td>ФИО клиента:</td><td>
<input type=text name="CustName" size="60" value="{$fixclient_data.company_full|escape:"html"}"><br>
</td></tr>
<tr><td>E-mail:</td><td>
<input type=text name="CustEMail" size="60" value="{$fixclient_data.email|escape:"html"}"><br>
</td></tr><tr><td>Содержание заказа:</td><td>
<textarea rows="10" name="OrderDetails" cols="60">Деньги на счёт клиента &#8470;{$fixclient_data.id} ({$fixclient_data.company_full}, {$fixclient_data.client})</textarea><br>
</td></tr></table>

CustAddr: <input type=text name="CustAddr" size="60">


<input type=submit value="Платить через Яндекс.Деньги"><br>
</form>
Поля формы с префиксом "wbp" являются служебными и не подлежат корректировке.<br>
Поля CustomerNumber, Sum, CustName, CustAddr, CustEMail, OrderDetails доступны клиенту на странице оформления заказа.<br>
Поля с другими параметрами заказа (CustName, CustAddr, CustEMail, OrderDetails) выбираются магазином и в обязательном порядке согласовываются с техническим специалистом компании Яндекс.Деньги (shopadmin@yamoney.ru).<br>
