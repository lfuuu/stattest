<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
{literal}<STYLE>BODY                             { margin: 0px;}
BODY, TD, FONT                          {FONT-FAMILY: Tahoma, Arial, Helvetica, sans-serif;}

h1                                      {margin: 0 0 0 0; padding: 0; font-size: 18px; font-weight: bold; line-height: 1.1}
h2                                      {margin: 0 0 0 0; font-size: 18px; font-weight: normal;}
h3                                      {margin: 1em 0 0.5em 0; font-size: 18px; font-weight:bold}
h4                                      {margin: 0em 0 0.5em 0; font-size: 110%; FONT-FAMILY: Arial, Helvetica, sans-serif;}

p                                       {margin: 0 0 1em 0;}
.z13 {FONT-SIZE: 13px;}
.z12 {FONT-SIZE: 12px;}
.z11 {FONT-SIZE: 8px;}
.z10 {FONT-SIZE: 10px;}

table.default                           {font-size: 1.0em; color: #333333;}
table.default td                        {padding: 6px;}


div.prim                                {color: #666666; font-size: 10px; border-top: solid #999999 1px; margin-top: 10px; margin-bottom:
20px; padding-top: 10px; line-height: 100%}

table.price2 td {border-bottom: solid 1px #B0B6CB; border-right: solid 1px #B0B6CB; padding: 2px; font-family: Tahoma, Arial, sans-serif;
font-size: 8px; }
table.price2 td.right {padding: 12px; padding-top:0; font-family: Tahoma, Arial, sans-serif; font-size: 9px; }

table.price2 tr.header {font-size: 9px;}
table.price2 td.header {vertical-align:bottom; font-family: Arial, Helvetica, sans-serif; font-size: 9px; border-bottom: solid 3px #333;
background-color:#FFFFFF;}
table.price2 td.h2 {border-bottom: 0;}

table.price2 th {margin: 1em 0 0.5em 0; font-size: 12px;}

.outset                                 {BORDER: black 1px solid;}
img.outset                              {BORDER: black 1px solid;}


ul.nomargin {margin-left: 0; margin-right: 0; padding: 0; }
ul.nomargin li { margin-left: 1.7em; }
ul.nomargin>li { margin: 0.4em 0 0.4em 1.4em; }

ul.snomargin {margin-left: 0; margin-right: 0; padding: 0; }
ul.snomargin li { margin-left: 1.7em; }
ul.snomargin>li { margin: 0.4em 0 0.4em 1.4em; }

hr { display: block; margin-left: 0px; margin-right: 0px; border: 0 none #000; color: #000; background-color: #000; height: 1px; }


.input { width: 80%; }
.textarea { width: 99%; }

.inputdisabled {background-color: #f5f5f5; border: 0; color: #000000; font-size: 18px;}

div.scrollcheck {
        border: 1px solid #DCDCDC;
        margin: 0px;
        margin-top: 3px;
        margin-left: 25px;
        padding: 4px;

        background-color: #F5F5F5;
        font-family: Verdana, Geneva, Arial, Helvetica, sans-serif;
        font-size: 11px;
        }

</STyle>{/literal}
</HEAD>
<BODY style="font-family: Arial, Helvetica, sans-serif; font-size: 10px" text="#000" vLink="#000099" aLink="#000000" link="#000099"
bgColor="#FFFFFF">
<body style="padding:0">
<table cellspacing=0 cellpadding=0 border=0 width=100% class=z11>
<tbody><tr><td valign=top>
<tr>
<td width="33%">&nbsp;</td>
<td width=3%>&nbsp;</td>
<td width="60%" align="right">
Унифицированная форма &#8470; ТОРГ-12. Утверждена постановлением Госкомстата России от 25.12.98 &#8470; 132 </td>
</tr>
</tbody></table>
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0" class=z11>
<tr>
<td valign=top><table width="100%" border="0" cellpadding="0" cellspacing="0" class=price2>
<tr>
<td width="129">&nbsp;</td>
<td width="29">&nbsp;</td>
<td width="87">&nbsp;</td>
<td width="87">&nbsp;</td>
<td width="87">&nbsp;</td>
<td width="91">&nbsp;</td>
<td >&nbsp;</td>
<td width="41">Коды</td>
</tr>
<tr>
<td align="right" rowspan=4>Грузоотправитель:</td>
<td colspan="5"  rowspan=4>

{*** from ***}
{capture name=from}

{if $bill_client.firma=='all4geo'}
  ООО &quot;Олфогео&quot;, 115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8<br />
  р/с 40702810038110016607 в ОАО Сбербанк России, Корр/с 30101810400000000225<br />ИНН/КПП 7727752091/772401001 БИК 044525225
{elseif $bill_client.firma=='all4net'}
    
	ООО &quot;Олфонет&quot;, 
    {if $bill.ts >= strtotime("2013-08-13")}
        117452, г.Москва, Балаклавский проспект, д.20, к.4 кв.130, тел: 638-7777<br />
    {else}
        117218, г.Москва, ул. Большая Черемушкинская д. 25, стр. 97, тел: 638-7777<br />
    {/if}
    р/с 40702810500540000002 в ОАО &laquo;УРАЛСИБ&raquo;, Корр/с 30101810100000000787<br />ИНН/КПП 7727731060/772701001 БИК 044525787


{elseif $bill_client.firma=='ooocmc'}
	{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}Общество с ограниченной ответственностью "Си Эм Си" (ООО "Си Эм Си"){else}ООО "Си Эм Си"{/if}, 117218, г. Москва, ул. Большая Черемушкинская, д. 25, стр. 97   <br />
р/с 40702810800540001507 в ОАО "УРАЛСИБ", Корр/с 30101810100000000787<br />ИНН/КПП продавца: 7727701308/772701001 БИК 044525787
{elseif $bill_client.firma=='mcn_telekom'}

	{$firm.name}, {$firm.address}<br />
р/с {$firm.acc} в {$firm.bank}, Корр/с {$firm.kor_acc}<br />ИНН/КПП продавца: {$firm.inn}/{$firm.kpp} БИК {$firm.bik}

{if false}
	<!--ООО &quot;МСН Телеком&quot;, 115487, г. Москва, 2-й Нагатинский пр-д, д.2, стр.8   <br />
р/с 40702810038110015462 в Московский банк Сбербанка России ОАО, г. Москва, Корр/с 30101810400000000225<br />ИНН/КПП продавца: 7727752084/772401001 БИК 044525225
    -->
{/if}

{elseif $bill_client.firma=='markomnet_service'}
	ООО &quot;Марктмнет сервис&quot;, 117574, Москва, Одоевского проезд, д.3, к.7  <br />
р/с 40702810538110016699 в ОАО &laquo;Сбербанк России&raquo; г. Москва, Корр/с 30101810400000000225<br />ИНН/КПП продавца: 7728802130/772801001 БИК 044525225
{elseif $bill_client.firma=='markomnet_new'}
	ООО &quot;Маркомнет&quot;, 117218, г. Москва, ул. Большая Черемушкинская, д. 25, стр. 97   <br />
р/с 40702810100540001508 в ОАО "УРАЛСИБ" г. Москва, Корр/с 30101810100000000787<br />ИНН/КПП продавца: 7727702076/772701001 БИК 044525787
{elseif $bill_client.firma=='ooomcn'}
	{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}Общество с ограниченной ответственностью "МСН" (ООО "МСН"){else}ООО "МСН"{/if}, 117574 г. Москва, Одоевского пр-д., д. 3, кор. 7, тел: (495) 950-5678 доб 159<br />
р/с 40702810538110011157 в Московский банк Сбербанка России ОАО, г. Москва, Корр/с 30101810400000000225<br />ИНН/КПП продавца: 7728638151/772801001 БИК 044525225
{else}
	{$firm.name}, {$firm.address}{if isset($firm.phone)}, тел: {$firm.phone}{/if}<br />
р/с:&nbsp;{$firm.acc} в {$firm.bank} Корр/с:&nbsp;{$firm.kor_acc}<br>
ИНН/КПП {$firm.inn}/{$firm.kpp}<br>
БИК:&nbsp;{$firm.bik}<br>

{/if}
{/capture}

{*** to ***}
{capture name=to}
{$bill_client.company_full}, {$bill_client.address_post}<br />
{$bill_client.bank_properties}<br /> ИНН/КПП {$bill_client.inn}/{$bill_client.kpp} БИК {$bill_client.bik}
{/capture}

{*** from_send ***}
{capture name=from_send}
  {if $bill_client.firma=='all4geo'}
    ООО &quot;Олфогео&quot;, 115487, г.Москва, Нагатинский 2-й проезд, дом 2, строение 8<br />
    р/с 40702810038110016607 в ОАО Сбербанк России, Корр/с 30101810400000000225<br />ИНН/КПП 7727752091/772401001 БИК 044525225
{elseif $bill_client.firma=='all4net'}
    ООО &quot;Олфонет&quot;, 
    {if $bill.ts >= strtotime("2013-08-13")} 
        117452, г.Москва, Балаклавский проспект, д.20, к.4 кв.130, тел: 638-7777<br />
    {else}
        117218, г.Москва, ул. Большая Черемушкинская д. 25, стр. 97, тел: 638-7777<br />
    {/if}
    р/с 40702810500540000002 в ОАО &laquo;УРАЛСИБ&raquo;, Корр/с 30101810100000000787<br />ИНН/КПП 7727731060/772701001 БИК 044525787

{elseif $bill_client.firma=='ooocmc'}
	{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}Общество с ограниченной ответственностью "Си Эм Си" (ООО "Си Эм Си"){else}ООО "Си Эм Си"{/if}, 117218, г. Москва, ул. Большая Черемушкинская, д. 25, стр. 97<br />
р/с 40702810800540001507 в ОАО "УРАЛСИБ", Корр/с 30101810100000000787<br />ИНН/КПП продавца: 7727701308/772701001 БИК 044525787
{elseif $bill_client.firma=='mcn_telekom'}
	{$firm.name}, {$firm.address}<br />
р/с {$firm.acc} в {$firm.bank}, Корр/с {$firm.kor_acc}<br />ИНН/КПП продавца: {$firm.inn}/{$firm.kpp} БИК {$firm.bik}

{elseif $bill_client.firma=='markomnet_service'}
	ООО &quot;Маркомнет сервис&quot;, 117574, Москва, Одоевского проезд, д.3, к.7<br />
р/с 40702810538110016699 в ОАО &laquo;Сбербанк России&raquo; г. Москва, Корр/с 30101810400000000225<br />ИНН/КПП продавца: 7728802130/772801001 БИК 044525225
{elseif $bill_client.firma=='markomnet_new'}
	ООО &quot;Маркомнет&quot;, 117218, г. Москва, ул. Большая Черемушкинская, д. 25, стр. 97<br />
р/с 40702810100540001508 в ОАО "УРАЛСИБ" г. Москва, Корр/с 30101810100000000787<br />ИНН/КПП продавца: 7727702076/772701001 БИК 044525787
{elseif $bill_client.firma=='ooomcn'}
	{if '2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)}Общество с ограниченной ответственностью "МСН" (ООО "МСН"){else}ООО "МСН"{/if}, 117574 г. Москва, Одоевского пр-д., д. 3, кор. 7, тел: (495) 950-5678 доб 159<br />
р/с 40702810538110011157 в Московский банк Сбербанка России ОАО, г. Москва, Корр/с 30101810400000000225<br />ИНН/КПП продавца: 7728638151/772801001 БИК 044525225
{else}
	{$firm.name}, {$firm.address}{if isset($firm.phone)}, тел: {$firm.phone}{/if}<br />
р/с:&nbsp;{$firm.acc} в {$firm.bank} Корр/с:&nbsp;{$firm.kor_acc}<br>
ИНН/КПП {$firm.inn}/{$firm.kpp}<br>
БИК:&nbsp;{$firm.bik}<br>
{/if}
{/capture}

{*** to_pay ***}
{capture name=to_pay}
{$bill_client.company_full}, {$bill_client.address_jur}<br />
{$bill_client.bank_properties}<br />ИНН/КПП {$bill_client.inn}/{$bill_client.kpp} БИК {$bill_client.bik}
{/capture}

{if $bill.is_rollback}{$smarty.capture.to}{else}{$smarty.capture.from}{/if}
</td>
</tr>
<tr>
<td align="right">Форма по ОКУД </td>
<td>0330212</td>
</tr>
<tr>
<td align="right">по ОКПО </td>
<td>58543248</td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
<tr>
    <td colspan="2" align="right">Структурное подразделение:</td>
    <td colspan="4">&nbsp;</td>
    <td align="right">&nbsp;</td>
    <td>&nbsp;</td>
</tr>
<tr>
    <td align="right" rowspan=4>Грузополучатель:</td>
    <td colspan="5" rowspan=4>{if !$bill.is_rollback}{$smarty.capture.to}{else}{$smarty.capture.from}{/if}</td>
</tr>
<tr>
    <td align="right"><div style="font-size:8px">Вид деятельности по ОКДП</div></td>
    <td>&nbsp;</td>
</tr>
<tr>
    <td align="right">по ОКПО</td>
    <td>&nbsp;{if $bill_client.okpo}{$bill_client.okpo}{/if}</td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
<tr>
<td align="right" rowspan=4>Поставщик:</td>
<td colspan="5" rowspan=4>{if $bill.is_rollback}{$smarty.capture.to_pay}{else}{$smarty.capture.from_send}{/if}</td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right">по ОКПО</td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right" rowspan=4>Плательщик:</td>
<td colspan="5" rowspan=4>{if !$bill.is_rollback}{$smarty.capture.to_pay}{else}{$smarty.capture.from_send}{/if}</td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td>&nbsp;</td>
<td></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td></td>
</tr>
<tr>
<td align="right">по ОКПО</td>
<td>&nbsp;{if $bill_client.okpo}{$bill_client.okpo}{/if}</td>
</tr>


<tr>
<td align="right">Основание:</td>
<td colspan="5">{if $bill.is_rollback}Возврат{else}Основной договор{/if} </td>
<td align="right"> номер</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>&nbsp;</td>
<td colspan="5" align="center">&nbsp;</td>
<td align="right"><div style="font-size:9px">Транспортная накладная номер</div></td>
<td>&nbsp;</td>
</tr>
<tr>
<td colspan=3 align=right>наименование документа (договор, контракт, заказ-наряд)</td>
<td align="center">Номер документа </td>
<td align="center" colspan="2">Дата составления </td>
<td align="right">дата </td>
<td>&nbsp;</td>
</tr>
<tr>
<td colspan=3 rowspan=2 align=right style="font-size: 12pt; font-weight: bold">ТОВАРНАЯ НАКЛАДНАЯ </td> <td rowspan=2 align="center"
style="font-size: 10pt; font-weight: bold">{$bill.bill_no}</td>
<td rowspan=2 align="center" colspan="2" style="font-size: 10pt; font-weight: bold">
    {if !$without_date_date}
        {$bill.bill_date|mdate:"d.m.Y г."}
    {else}
        {$without_date_date|mdate:"d.m.Y г."}
    {/if}</td>
<td valign=top align=right>Вид операции</td>
<td valign=top align=right>&nbsp;</td>
<!--/tr>
</table></td>
<td valign=top><table border="0" align="right" cellpadding="0" width=100% cellspacing="0" class=price2>
<tr>
<td >&nbsp;</td>
<td width="41">Коды</td>
</tr>
<tr>
<td align="right">Форма по ОКУД </td>
<td>0330212</td>
</tr>
<tr>
<td align="right">по ОКПО </td>
<td>58543248</td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right"><div style="font-size:8px">Вид деятельности по ОКДП</div></td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right">по ОКПО</td>
<td>&nbsp;</td>
</tr>
<tr style="height: 30px;">
<td align="right"><span style="font-size: 3px;">&nbsp;</span></td>
<td><span style="font-size: 3px;">&nbsp;</span></td>
</tr>
<tr>
<td align="right">по ОКПО</td>
<td >&nbsp;</td>
</tr>
<tr style="height: 31px;">
<td align="right"><span style="font-size: 3px;">&nbsp;</span></td>
<td><span style="font-size: 3px;">&nbsp;</span></td>
</tr>
<tr>
<td align="right">по ОКПО</td>
<td >&nbsp;</td>
</tr>
<tr>
<td align="right">&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right">номер</td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right">дата</td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right"><div style="font-size:9px">Транспортная накладная номер</div></td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right">дата</td>
<td>&nbsp;</td>
</tr>
<tr>
<td align="right">Вид операции </td>
<td>&nbsp;</td>
</tr>
</table></td-->
</tr>
</table>
<hr>
<table width="100%" border="0" cellpadding="3" cellspacing="1" class=price2>
<tr align="center" class=header>
<td class=header>Номер по порядку </td>
<td class=header width="250" colspan="2">Товар</td>
<td class=header width="128" colspan="2">Единица измерения </td>
<td class=header>Вид упаковки </td>
<td class=header width="128" colspan="2">Количество </td>
<td class=header>Масса брутто </td>
<td class=header>Кол-во (масса нетто)</td>
<td class=header>Цена, руб. коп. </td>
<td class=header>Сумма без учета НДС, руб. коп. </td>
<td class=header width="128" colspan="2">НДС</td>
<td class=header>Сумма с учетом НДС, руб. коп. </td>
</tr>
<tr align="center">
<td>&nbsp; </td>
<td >наименование, характеристика, сорт, артикул товара </td>
<td >код </td>
<td>наиме- нование </td>
<td>код по ОКЕИ </td>
<td>&nbsp; </td>
<td>в одном месте </td>
<td>мест, штук </td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>ставка, %</td>
<td>сумма, руб. коп.</td>
<td>&nbsp;</td>
</tr>
<tr align="center">
<td class=header>1 </td>
<td class=header>2 </td>
<td class=header>3 </td>
<td class=header>4 </td>
<td class=header>5 </td>
<td class=header>6 </td>
<td class=header>7 </td>
<td class=header>8 </td>
<td class=header>9 </td>
<td class=header>10 </td>
<td class=header>11 </td>
<td class=header>12 </td>
<td class=header>13 </td>
<td class=header>14 </td>
<td class=header>15 </td>
</tr>
{foreach from=$bill_lines item=line key=key}
<tr>
<td align="right">{$key+1}</td>
<td width="192">{$line.item}</td>
<td >&nbsp;</td>
<td align="center">шт</td>
<td align="center">796 </td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td align="right">{$line.amount|round:2}</td>
<td align='right'>{$line.outprice|round:2}</td>
<td align="right">{$line.sum_without_tax|round:2}</td>
<td align="right">{$line.line_nds|round:0}</td>
<td align="right">{$line.sum_tax|round:2}</td>
<td align="right">{$line.sum|round:2}</td>
</tr>
{/foreach}

<tr>
<td colspan="7" align="right">Итого</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td align='right'>{$total_amount|round:2}</td>
<td align="right">Х </td>
<td align="right">{$line.sum_without_tax|round:2}</td>
<td align="right">Х </td>
<td align="right">{$line.sum_tax|round:2}</td>
<td align="right">{$bill.sum|round:2}</td>
</tr>
<tr>
<td colspan="7" align="right">Всего по накладной</td>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td align='right'>{$total_amount|round:2}</td>
<td align="right">Х </td>
<td align="right">{$line.sum_without_tax|round:2}</td>
<td align="right">Х </td>
<td align="right">{$line.sum_tax|round:2}</td>
<td align="right">{$bill.sum|round:2}</td>
</tr>
</table>
<hr>
<table border=0 cellpadding=0 cellspacing=0 width="100%" class=z10>
<tbody><tr>
<td width=50%>
<table cellspacing=0 cellpadding=0 border=0 width=100% class=z10>
<tbody><tr>
<td valign=top>Товарная накладная имеет приложение на</td>
<td>____________</td>
<td>листах</td>
</tr><tr>
<td>и содержит</td>
<td>____________</td>
<td>порядковых номеров записей</td>
</tr></tbody></table>
</td><td style="border-left: solid 1px #999">&nbsp;</td>
<td>
<table cellspacing=0 cellpadding=0 border=0 width=100% class=z10>
<tbody><tr>
<td rowspan=2>Всего мест</td>
<td>Масса груза (нетто)</td>
<td>__________________________________________________</td>
</tr><tr>
<td>Масса груза (брутто)</td>
<td>__________________________________________________</td>
</tr>
</tbody></table>
</td></tr></tbody></table>
<hr>
<table cellspacing=0 cellpadding=0 border=0 width=100% class=z11><tbody>

<tr>
<td align="left" valign="top" colspan="8" rowspan="2">Всего отпущено на сумму: {$bill.sum|wordify:'RUB'}</td>
<td>&nbsp;</td>
<td align="left" valign="top" colspan="2" style="border-left:1px solid">По доверенности &#8470;</td>
<td align="center" valign="bottom" style="border-bottom:1px solid">&nbsp;</td>
<td align="center" valign="bottom">от&nbsp;"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"</td>
<td align="center" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="left" valign="bottom">20&nbsp;&nbsp;&nbsp;года</td>
</tr>
<tr>
<td>&nbsp;</td>
<td align="left" valign="top" style="border-left:1px solid">выданной</td>
<td align="center" valign="bottom" colspan="6" style="border-bottom:1px solid">&nbsp;</td>
</tr>
<tr>
<td colspan="9">&nbsp;</td>
<td style="border-left:1px solid">&nbsp;</td>
<td align="center" valign="top" colspan="6" class="cod7">кем, кому (организация, место работы, должность, фамилия, и. о.)</td>
</tr>
<tr>
<td align="left" valign="top" colspan="2"><nobr>Отпуск груза разрешил</nobr></td>
<td align="center" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="left" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="center" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td>&nbsp;</td>
<td align="left" valign="top" style="border-left:1px solid">&nbsp;</td>
<td align="center" valign="bottom" colspan="6">&nbsp;</td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
<td align="center" valign="top" colspan="2" class="cod7">должность</td>
<td align="center" valign="top" colspan="2" class="cod7">подпись</td>
<td align="center" valign="top" colspan="2" class="cod7">расшифровка подписи</td>
<td>&nbsp;</td>
<td align="left" valign="top" style="border-left:1px solid">Груз принял</td>
<td align="left" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="left" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="left" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
</tr>
<tr>
<td colspan="3" align="right" class="cod9"><b>Главный (старший) бухгалтер</b></td>
<td align="center" valign="bottom" colspan="3" style="border-bottom:1px solid">&nbsp;</td>
<td align="center" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td>&nbsp;</td>
<td style="border-left:1px solid">&nbsp;</td>
<td align="center" valign="top" colspan="2" class="cod7">должность</td>
<td align="center" valign="top" colspan="2" class="cod7">подпись</td>
<td align="center" valign="top" colspan="2" class="cod7"><nobr>расшифровка подписи</nobr></td>
</tr>
<tr>
<td colspan="3">&nbsp;</td>
<td align="center" valign="top" colspan="3" class="cod7">подпись</td>
<td align="center" valign="top" colspan="2" class="cod7"><nobr>расшифровка подписи</nobr></td>
<td>&nbsp;</td>
<td colspan="7" style="border-left:1px solid">&nbsp;</td>
</tr>
<tr>
<td align="left" valign="top" colspan="2"><nobr>Отпуск груза произвел</nobr></td>
<td align="center" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="left" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="center" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td>&nbsp;</td>
<td align="left" valign="top" style="border-left:1px solid"><nobr>Груз получил</nobr></td>
<td align="left" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="left" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
<td align="left" valign="bottom" colspan="2" style="border-bottom:1px solid">&nbsp;</td>
</tr>
<tr>
<td colspan="2">&nbsp;</td>
<td align="center" valign="top" colspan="2" class="cod7">должность</td>
<td align="center" valign="top" colspan="2" class="cod7">подпись</td>
<td align="center" valign="top" colspan="2" class="cod7">расшифровка подписи</td>
<td>&nbsp;</td>
<td align="left" valign="top" style="border-left:1px solid">&nbsp;</td>
<td align="center" valign="top" colspan="2" class="cod7">должность</td>
<td align="center" valign="top" colspan="2" class="cod7">подпись</td>
<td align="center" valign="top" colspan="2" class="cod7"><nobr>расшифровка подписи</nobr></td>
</tr>
<tr>
<td>&nbsp;</td>
<td align="center" valign="absmiddle">М.П.</td>
<td align="center" valign="bottom" colspan="4">"&nbsp;&nbsp;&nbsp;&nbsp;" ____________________ 20&nbsp;&nbsp;&nbsp;&nbsp;года</td>
<td align="center" valign="top" colspan="3">&nbsp;</td>
<td style="border-left:1px solid">&nbsp;</td>
<td align="center" valign="absmiddle">М.П.</td>
<td align="center" valign="bottom" colspan="4"><nobr>"&nbsp;&nbsp;&nbsp;&nbsp;" ____________________ 20&nbsp;&nbsp;&nbsp;&nbsp;года</nobr></td>
<td align="center" valign="top">&nbsp;</td>
</tr>
</tbody></table>
</body>
</html>
