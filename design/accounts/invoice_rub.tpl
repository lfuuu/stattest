<html>

<head>
<LINK title=default href="../../design/accounts/invoice.css" type=text/css rel=stylesheet>
<title>СЧЕТ-ФАКТУРА N {$invoice.invoice_no} от {$invoice.invoice_date}</title>
</head>

<body bgcolor="#FFFFFF" text="#000000">
<div align="center"><center>
<table border="0" cellpadding="0" cellspacing="15">
  <tr>
  {if $client.firma eq "mcn"}
  <td valign="top" width="50%">Продавец: <strong>ООО "Эм Си Эн"</strong><br>
    Адрес: <strong>113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130</strong><br>
    ИНН/КПП продавца: <strong>7727508671 / 772701001</strong><br>
    Грузоотправитель и его адрес: <strong>ООО "Эм Си Эн"</strong><br>
    <strong>113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130</strong><br>
    Грузополучатель и его адрес: <strong>{$client.company_full}</strong><br>
    <strong>{$client.address_post}</strong><br>
    К платежно-расчетному документу N
{if $print_pay}<strong>{$invoice.pay_no} от {$invoice.pay_date}</strong>{/if}<br>
    Покупатель: <strong>{$client.company_full}</strong><br>
    Адрес: <strong>{$client.address_jur}</strong><br>
    Телефон: <strong>{$client.phone}</strong><br>
    ИНН/КПП покупателя: <strong>{$client.inn}&nbsp;/{$client.kpp}</strong><br>
    Дополнение: <strong>к счету N: {$invoice.bill_no}</strong></td>

  {elseif $client.firma eq 'markomnet_new'}
    <td valign="top" width="50%">Продавец: <strong>ООО "МАРКОМНЕТ"</strong><br>
    Адрес: <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong><br>
    Телефон: <strong>(495) 638-638-4</strong><br>
    ИНН/КПП продавца: <strong>7727702076&nbsp;/&nbsp;772701001</strong><br>
    Грузоотправитель и его адрес: <strong>ООО "МАРКОМНЕТ"</strong><br>
    <strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong><br>
    Грузополучатель и его адрес: <strong>{$client.company_full}</strong><br>
    <strong>{$client.address_post}</strong><br>
    К платежно-расчетному документу N
{if $print_pay}<strong>{$invoice.pay_no} от {$invoice.pay_date}</strong>{/if}<br>
    Покупатель: <strong>{$client.company_full}</strong><br>
    Адрес: <strong>{$client.address_jur}</strong><br>
    Телефон: <strong>{$client.phone}</strong><br>
    ИНН/КПП покупателя: <strong>{$client.inn}&nbsp;/{$client.kpp}</strong><br>
    Дополнение: <strong>к счету N: {$invoice.bill_no}</strong></td>
{else}
    <td valign="top" width="50%">Продавец: <strong>ООО "МАРКОМНЕТ"</strong><br>
    Адрес: <strong>123458, г. Москва, Таллинская ул., д.2/282</strong><br>
    Телефон: <strong>(095) 950-5678</strong><br>
    ИНН/КПП продавца: <strong>7734246040&nbsp;/&nbsp;773401001</strong><br>
    Грузоотправитель и его адрес: <strong>ООО "МАРКОМНЕТ"</strong><br>
    <strong>123458, г. Москва, Таллинская ул., д.2/282</strong><br>
    Грузополучатель и его адрес: <strong>{$client.company_full}</strong><br>
    <strong>{$client.address_post}</strong><br>
    К платежно-расчетному документу N
{if $print_pay}<strong>{$invoice.pay_no} от {$invoice.pay_date}</strong>{/if}<br>
    Покупатель: <strong>{$client.company_full}</strong><br>
    Адрес: <strong>{$client.address_jur}</strong><br>
    Телефон: <strong>{$client.phone}</strong><br>
    ИНН/КПП покупателя: <strong>{$client.inn}&nbsp;/{$client.kpp}</strong><br>
    Дополнение: <strong>к счету N: {$invoice.bill_no}</strong></td>
  {/if}
    <td align=right valign="top" width="50%">
    <small>    Приложение N1<br>
    к Правилам ведения журналов учета полученных и выставленных счетов-фактур,<br>
    книг покупок и книг продаж при расчетах но налогу на добавленную стоимость,<br>
    утвержденным постановлением правительства Российской Федерации от 2 декабря 200 г. N 914<br>
    (в редакции постановлений Правительства Российской Федерации<br>
    от 10 марта 2001 г. N 189, от 27 июля 2002 г. N 575, от 16 февраля 2004 г. N 84{if $invnew}, от 11.05.2006 N 283{/if})
    </small></td>

  </tr>
  <tr>

    <td colspan="2"><p align="center"><strong>СЧЕТ-ФАКТУРА N&nbsp;{$invoice.invoice_no} от {$invoice.invoice_date}</strong></p>
    Валюта: руб.
    <div align="center"><center><table border="1" cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <th>Наименование<br>товара<br>(описание выполненных работ, оказанных услуг){if $invnew},<br>имущественного права{/if}</th>
        <th>Еди-<br>ница<br>изме-<br>рения</th>
        <th>Коли-<br>чество</th>
        <th>Цена<br>(та-<br>риф)<br>за еди-<br>ницу изме-<br>рения</th>
        <th>Стои-<br>мость<br>товаров<br>(работ,<br>услуг){if $invnew},<br>имущественных<br>прав{/if}, всего без<br>налога</th>
        <th>в том<br>чис-<br>ле<br>акциз</th>
        <th>Нало-<br>говая<br>ставка</th>
        <th>Сумма<br>нало-<br>га</th>
        <th>Стоимость<br>товаров<br>(работ,<br>услуг){if $invnew},<br>имущественных прав{/if},<br>всего с<br>учетом<br>налога</th>
        <th>Стра-<br>на проис-<br> хожде-<br> ния</th>
        <th>Номер<br> гру-<br> зовой<br> тамо-<br> женной<br> декла-<br> рации</th>
      </tr>
      <tr>
        <td align="center">1</td>
        <td align="center">2</td>
        <td align="center">3</td>
        <td align="center">4</td>
        <td align="center">5</td>
        <td align="center">6</td>
        <td align="center">7</td>
        <td align="center">8</td>
        <td align="center">9</td>
        <td align="center">10</td>
        <td align="center">11</td>
      </tr>
{foreach from=$rows item=row key=key}
      <tr>
        <td>{$row.item}</td>
        <td align="center">{$row.ediz}</td>
        <td align="center">{$row.amount}</td>
        <td align="center">{$row.price}</td>
        <td align="center">{$row.sum}</td>
        <td align="center">-</td>
        <td align="center">{$row.tax}%</td>
        <td align="center">{$row.tax_sum}</td>
        <td align="center">{$row.sum_plus_tax}</td>
        <td align="center">-</td>
        <td align="center">-</td>
      </tr>
{/foreach}
     <tr>
        <td colspan=7><b>Всего к оплате<b></td>
        <td align="center">{$invoice.tax_sum}</td>
        <td align="center">{$invoice.sum_plus_tax}</td>
        <td colspan=2>&nbsp;</td>
      </tr>

    </table>
    </center></div>
<br>
    <div align="center">
    <table border="0" cellpadding="0" cellspacing="5" align="left">
      <tr >
        <td><p align="right">Руководитель&nbsp;организации:</td>
        <td>
        {if $client.stamp == 1 and $client.firma != 'mcn'}<img src="https://stat.mcn.ru/img/sign1.gif" width="155" height="80" border="0" alt="" align="top">{else}<br>________________________________<br><br>{/if}</td>
    <td>/Мельников А.К./</td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td><p align="right">&nbsp;Гл.&nbsp;бухгалтер</td>
    <td>{if $client.stamp == 1 and $client.firma != 'mcn'}<img src="https://stat.mcn.ru/img/sign2.gif" width="240" height="60" border="0" alt="" align="top">{else}<br>________________________________<br><br>{/if}</td>
        <td>/Антонова Т.С./</td>
      </tr>
      <tr>
        <td></td>
        <td align="center"><small>(подпись)</small></td>
        <td align="center"><small>(ф.и.о.)</small></td>
    <td></td>
        <td></td>
        <td align="center"><small>(подпись)</small></td>
        <td align="center"><small>(ф.и.о.)</small></td>
      </tr>
      <tr>
        <td colspan=4><p><br>Индивидуальный предприниматель ___________________&nbsp;&nbsp;&nbsp;&nbsp; _______________________</p>
    </td>
	<td colspan=3><p><br>&nbsp; &nbsp;____________________________________________________________________</p></td>
      </tr>
	<tr>
<td></td><td align=center><small>(подпись)</small></td>
<td align=center><small>(ф.и.о.)</small></td>
<td></td>
<td align=center colspan=3><small>(реквизиты свидетельства о государственной регистрации индивидуального предпринимателя)</small></td>
</tr>
    </table>
    </div>
   </td>
  </tr>
</table>
</center></div>
<small>Примечание: Первый экземпляр - покупателю, второй экземпляр - продавцу.</small>
</body>
</html>
