<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Счёт &#8470;{$bill_no}</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<LINK title=default href="main.css" type="text/css" rel="stylesheet">
</HEAD>


<body>

<table width="100%">
<tr>
<td>
<p>Адрес доставки счета: {$address_post}<br>
Факс для отправки счета: {$fax}</p>

<p>

<b>Поставщик: ООО &laquo;МАРКОМНЕТ&raquo;</b><br>
ИНН:&nbsp;7734246040;&nbsp;&nbsp;КПП:&nbsp;773401001<br>
Адрес:&nbsp;123458, г.Москва, Таллинская ул., д.2/282<br>
Телефон: (095) 335-0893<br>
Факс: (095) 335-3723<br>
</p>
<p>
р/с:&nbsp;40702810300070015232 в АКБ &laquo;Пробизнесбанк&raquo;<br>
к/с:&nbsp;30101810600000000986<br>
БИК:&nbsp;044525986
</p>
</td>
<td>
<img border="0" src="logo2.gif" align="right" hspace=60 vspace=40>
<br>
{$client}<br>{$manager}
{if $bill_type=='connection'}
<br><a href='?bill_no={$bill_no}&client={$client}&show_advance={if !$show_advance}1{/if}'>+</a>
{/if}


</td>

</tr>
</table>

<hr>


<center><h2>Счёт &#8470;{$bill_no}</h2></center>



<p align=right>Дата: <b>{$bill_date_f}</b></p>

<hr>
<br>
<p><b>Плательщик: {$company_full}</b></p>


<table border="1" width="100%" cellspacing="0" cellpadding="2" style="font-size: 15px;">
  <tbody>
<tr>
    <td align="center"><b> п/п</b></td>
    <td align="center"><b>Предмет счета</b></td>
    <td align="center"><b>Количество</b></td>

    <td align="center"><b>Стоимость,&nbsp;$</b></td>
    <td align="center"><b>Сумма,&nbsp;$</b></td>
</tr>

{foreach from=$lines item=line key=key}

<tr>
		<td align="right">{$key}></td>
		<td>{$line.item}</td>

		<td align="center">{$line.amount}</td>
		<td align="right">{$line.price}&nbsp;</td>
		<td align="right">{$line.sum}</td>
</tr>

{/foreach}
{foreach from=$totals item=item}
<tr>
    <td colspan="4">
      <p align="right"><b>{$item.item}</b></p></td>

    <td align="right">{$item.sum}</td>
    
</tr>
{/foreach}

</tbody></table>
<br>
<p><i>Сумма прописью:  {$sum_in_words}</i></p>

<p style="font-size:11px">Оплата производится в рублях по курсу ЦБ РФ на день платежа плюс <%usd_rate_percent%>%</p>

<table border="0" width="100%" cellspacing="1" cellpadding="0">
  <tbody><tr>
    <td width="33%" valign="top"><br><br>Директор
    	
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>Главный бухгалтер</p></td>
    <td width="33%" valign="top">
      <p align="center"><img src="../img/sign1.gif" width="155" height="80" border="0" alt="" align="top">
      <img src="../img/sign2.gif" width="240" height="60" border="0" alt="" align="top">
      <img src="../img/stamp1.gif" width="150" height="150" border="0" alt="">
      </p></td>
    <td width="34%" valign="top"><br><br>/ Мельников А.К. /
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>/ Антонова Т.С./</p></td>
      <td></td>
  </tr>
</tbody></table>

<small>
Примечание:
При отсутствии оплаты счета до конца текущего месяца услуги по договору будут приостановлены до полного погашения задолженности.
</small>
</body>
</html>
