<html>

<head>
<LINK title=default href="../../design/accounts/invoice.css" type=text/css rel=stylesheet>
<title>Акт N {$invoice.invoice_no} от {$invoice.invoice_date}</title>
</head>

<body bgcolor="#FFFFFF" text="#000000">

<strong>
{if $client.firma eq "mcn"}OOO "Эм Си Эн"
{else}ООО "МАРКОМНЕТ"
{/if}
</strong>
<br>
Адрес: <strong>{if $client.firma eq "mcn"}113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130{else}123458, г. Москва, Таллинская ул., д.2/282{/if}</strong>
<br>
Телефон: <strong>(495) 950-5678</strong>
<br>
<br>
Заказчик: <strong>{$client.company_full}</strong>

<div align="center"><center>
<h2>
Акт N {$invoice.invoice_no} от {$invoice.invoice_date}
</h2>

<br>
<table border="0" cellpadding="0" cellspacing="15">
  <tr>

    <div align="center"><center><table border="1" cellpadding="3" cellspacing="0" width="100%">
      <tr>
        <th>NN<br>п/п</th>
        <th>Наименование работы (услуги)</th>
        <th>Ед.<br>изм.</th>
        <th>Коли-<br>чество</th>
        <th>Цена</th>
        <th>Сумма</th>
      </tr>
<!-- lines -->
<!-- line -->
{foreach from=$rows item=row key=key}
      <tr>
        <td align="center">{$row.line}</td>
        <td>{$row.item}</td>
        <td align="center">{$row.ediz}</td>
        <td align="center">{$row.amount}</td>
        <td align="center">{$row.price}</td>
        <td align="center">{$row.sum}</td>
      </tr>
{/foreach}

      <tr>
        <td colspan=5 align="right"><b>Итого:</b></td>
        <td align="right">{$invoice.sum}</td>
      </tr>
      <tr>
        <td colspan=5 align="right"><b>Итого НДС:</b></td>
        <td align="right">{$invoice.tax_sum}</td>
      </tr>
      <tr>
        <td colspan=5 align="right"><b>Всего (с учетом НДС):</b></td>
        <td align="right">{$invoice.sum_plus_tax}</td>
      </tr>
    </table>
    </center></div>
    <br>
    Всего оказано услуг на сумму: {$sum_plus_tax_in_words}, в т.ч. НДС: {$tax_sum_in_words}<br>
    <br>
    Вышеперечисленные услуги выполнены полностью и в срок. Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.
    <br>
    <br>
    <br>
    <br>
    <div style="position:relative; left:-80px; top:80px; z-index:1">
    {if $client.stamp == 0 or $client.firma == 'mcn'}<br><br><br>
    {else}
        <img src="https://stat.mcn.ru/img/stamp1.gif" width="150" height="150" border="0" alt="">
    {/if}
    </div>
    {if $client.stamp == 0 or $client.firma == 'mcn'}
     {assign var="pos" value="0"}
     {else}
     {assign var="pos" value="-150"}
    {/if}
    <div style="position:relative; top:{$pos}px; z-index:10">
    <table border="0" cellpadding="0" cellspacing="5">
      <tr>
        <td><p align="right">Исполнитель</td>
    <td>{if $client.stamp == 0 or $client.firma == 'mcn'}
        <br><br>______________________________<br><br>
        {else}
        <img src="https://stat.mcn.ru/img/sign1.gif" width="155" height="80" border="0" alt="" align="top">
        {/if}
    </td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td><p align="right">Заказчик</td>
    <td>______________________________</td>
      </tr>
      <tr>
        <td></td>
        <td align="center"><small>(подпись)</small></td>
    <td></td>
        <td></td>
        <td align="center"><small>(подпись)</small></td>
      </tr>
      <tr>
        <td></td>
        <td align="center"><br><br>М.П.</td>
    <td></td>
        <td></td>
        <td align="center"><br><br>М.П.</td>
      </tr>
    </table>
    </div>
   </td>
  </tr>
</table>
</center></div>
</body>
</html>
