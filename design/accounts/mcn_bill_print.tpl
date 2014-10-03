<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Счёт &#8470;{$bill_no}</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<LINK title=default href="main.css" type="text/css" rel="stylesheet">
<SCRIPT>
	function print_bill(){ldelim}
		window.print();
	{rdelim};
	function my_onafterprint(){ldelim}
		var id={$id};
		var loc="print_bills.php?i={$id}&t={$type}";
		document.all.next.style.visibility="visible";
	{rdelim};
</SCRIPT>
</HEAD>


<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" onload="javascript: print_bill();" onafterprint="javascript: my_onafterprint();" >

<div id="next" style="visibility : hidden;">
<a href="print_bills.php?i={$id}&t={$type}">next</a>
</div>

<table width="100%">
<tr><td>


<p>Адрес доставки счета: {$address_post}<br>
Факс для отправки счета: {$fax}</p>

<p>

<b>Поставщик: ООО &laquo;Эм Си Эн&raquo;</b><br>
ИНН:&nbsp;7727508671 &nbsp;&nbsp;КПП:&nbsp;772701001<br>
Адрес:&nbsp;113452, г.Москва, Балаклавский пр-т, д.20, кор. 4 кв. 130<br>
Телефон: (095) 950-56-78<br>
Факс: (095) 335-37-23<br>
</p>
<p>
р/с:&nbsp;40702810600301422002 в ЗАО КБ &laquo;Ситибанк&raquo;<br>
к/с:&nbsp;30101810300000000202<br>
БИК:&nbsp;044525202<br>
</p>
</td>
<td>
<img border="0" src="?code={$code}&img=logo" align="right">
<br><br><br><br>
<table border="0" align="right">
<tr {if $totals[3].sum > 300 } bgcolor="#FFD6D6" {/if} ><td>Клиент</td><td>
{$client}</td>
</tr>
{if $manager eq 'bnv'}{assign var="color" value="#EEDCA9"}{/if}
{if $manager eq 'pma'}{assign var="color" value="#BEFFFE"}{/if}
<tr bgcolor="{$color}"><td>Менеджер</td>
<td width="50">{$manager}<td>
</tr>
</table>
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
		<td align="right">{$key+1}</td>
		<td>{$line.item}</td>

		<td align="center">{$line.amount}</td>
		<td align="right">{$line.price}&nbsp;</td>
		<td align="right">{$line.sum}</td>
</tr>

{/foreach}

<tr>
    <td colspan="4">
      <p align="right"><b>{$totals[1].item|replace:"*":""}</b></p></td>

    <td align="right">{$totals[1].sum}</td>
    
</tr>
<tr>
    <td colspan="4">
      <p align="right"><b>{$totals[2].item|replace:"*":""}</b></p></td>

    <td align="right">{$totals[2].sum}</td>
    
</tr>
<tr>
    <td colspan="4">
      <p align="right"><b>{$totals[3].item|replace:"*":""}</b></p></td>

    <td align="right">{$totals[3].sum}</td>
    
</tr>




</tbody></table>
<br>
<p><i>Сумма прописью:  {$sum_in_words}</i></p>

<p style="font-size:11px">Оплата производится в рублях по курсу ЦБ РФ на день платежа плюс {$usd_rate_percent}%</p>

<table border="0" width="100%" cellspacing="1" cellpadding="0">
  <tbody><tr>
    <td width="33%" valign="top"><br><br>Генеральный директор
    	
      <p>&nbsp;</p>
      <p>&nbsp;</p>
      <p>Главный бухгалтер</p></td>
    <td width="33%" valign="top">
      <p align="center"><br><br>
      _________________________________<br><br><br><br>
      _________________________________<br>
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
