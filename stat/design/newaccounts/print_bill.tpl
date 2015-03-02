<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE>Счёт &#8470;{$bill.bill_no}</TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<LINK title=default href="{if $is_pdf == '1'}{$WEB_PATH}{else}{$PATH_TO_ROOT}{/if}bill.css" type="text/css" rel="stylesheet">
</HEAD>


<body bgcolor="#FFFFFF" style="BACKGROUND: #FFFFFF" >


<table width="100%">
<tr><td>


<p>Адрес доставки счета: {$bill_client.address_post}<br>
Факс для отправки счета: {foreach from=$contact.fax item=item name=inner}{if $smarty.foreach.inner.iteration!=1}; {/if}{$item.data}{/foreach}</p>

<p>

{if $bill_client.firma eq 'all4geo'}
  <b>Поставщик: ООО &laquo;Олфогео&raquo;</b><br>
  ИНН:&nbsp;7727752091 &nbsp;&nbsp;КПП:&nbsp;772401001<br>
  Адрес:&nbsp;115487, г. Москва, Нагатинский 2-й проезд, дом 2, строение 8<br>
  <!-- Телефон: (495) 950-5678 доб. 159<br>
  Факс: (495) 638-50-17<br-->
  р/с:&nbsp;40702810038110016607 в ОАО Сбербанк России, г. Москва<br>
  к/с:&nbsp;30101810400000000225<br>
  БИК:&nbsp;044525225<br>

  {elseif $bill_client.firma eq 'ooocmc'}
<b>Поставщик: ООО &laquo;Си Эм Си&raquo;</b><br>
ИНН:&nbsp;7727701308 &nbsp;&nbsp;КПП:&nbsp;772701001<br>
Адрес:&nbsp;117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97<br>
Телефон: (495) 950-5678<br>
р/с:&nbsp;40702810800540001507 в ОАО &laquo;УРАЛСИБ&raquo;<br>
к/с:&nbsp;30101810100000000787<br>
БИК:&nbsp;044525787<br>

{elseif $bill_client.firma eq 'ooomcn'}
<b>Поставщик: ООО &laquo;МСН&raquo;</b><br>
ИНН:&nbsp;7728638151 &nbsp;&nbsp;КПП:&nbsp;772801001<br>
Адрес:&nbsp;117574, г.Москва, Одоевского пр-д, д.3, кор.7<br>
Телефон: (495) 950-5678 доб. 159<br>
Факс: (495) 638-50-17<br>
р/с:&nbsp;40702810538110011157 в Московский банк Сбербанка России ОАО, г. Москва<br>
к/с:&nbsp;30101810400000000225<br>
БИК:&nbsp;044525225<br>

{elseif $bill_client.firma eq 'all4net'}
<b>Поставщик: ООО &laquo;Олфонет&raquo;</b><br>
ИНН:&nbsp;7727731060 &nbsp;&nbsp;КПП:&nbsp;772701001<br>
    {if $bill.ts >= strtotime("2013-08-13")}
        Адрес:&nbsp;117452, г. Москва, Балаклавский проспект, д.20, к.4 кв.130<br>
    {else}
        Адрес:&nbsp;117218, Москва г, Черемушкинская Б. ул, дом 25, строение 97<br>
    {/if}
        Телефон: (495) 638-77-77<br>
        р/с:&nbsp;40702810500540000002 в ОАО "УРАЛСИБ"<br>
        к/с:&nbsp;30101810100000000787<br>
        БИК:&nbsp;044525787<br>

{elseif $bill_client.firma == 'markomnet_new'}
<b>Поставщик: ООО &laquo;МАРКОМНЕТ&raquo;</b><br>
ИНН:&nbsp;7727702076;&nbsp;&nbsp;КПП:&nbsp;772701001<br>
Адрес:&nbsp;117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97<br>
Телефон: (495) 638-638-4<br>
Факс: (495) 638-52-80<br>
р/с:&nbsp;40702810100540001508 в ОАО &laquo;УРАЛСИБ&raquo; Г. МОСКВА<br>
к/с:&nbsp;30101810100000000787<br>
БИК:&nbsp;044525787<br>

{elseif $bill_client.firma == 'mcn_telekom'}
<b>Поставщик: {$firm.name}</b><br>
ИНН:&nbsp;{$firm.inn} ;&nbsp;&nbsp;КПП:&nbsp;{$firm.kpp}<br>
Адрес:&nbsp;{$firm.address}<br>
Телефон: {$firm.phone}<br>
Факс: {$firm.fax}<br>
р/с:&nbsp;{$firm.acc} в {$firm.bank}<br>
к/с:&nbsp;{$firm.kor_acc}<br>
БИК:&nbsp;{$firm.bik}<br>


{elseif $bill_client.firma == 'mcm'}
<b>Поставщик: ООО &laquo;МСМ&raquo;</b><br>
ИНН:&nbsp;7727667833 ;&nbsp;&nbsp;КПП:&nbsp;772701001<br>
Адрес:&nbsp;117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97<br>
Телефон: (495) 950-58-41<br>
Факс: (499) 123-55-33<br>
р/с:&nbsp;40702810500540001425 в ОАО &laquo;БАНК УРАЛСИБ&raquo;, г.Москва<br>
к/с:&nbsp;30101810100000000787<br>
БИК:&nbsp;044525787<br>

{elseif $bill_client.firma == 'markomnet_service'}
<b>Поставщик: ООО &laquo;Маркомнет сервис&raquo;</b><br>
ИНН:&nbsp;7728802130 ;&nbsp;&nbsp;КПП:&nbsp;772801001<br>
Адрес:&nbsp;117574, Москва, Одоевского проезд, д.3, к.7<br>
Телефон: (495) 638-63-84<br>
Факс: (495) 638-52-80<br>
р/с:&nbsp;40702810538110016699 в ОАО &raquo;Сбербанк России&raquo; г. Москва<br>
к/с:&nbsp;30101810400000000225<br>
БИК:&nbsp;044525225<br>

{elseif $bill_client.firma=='mcn'}{* || ($bill_client.nal=='beznal' && $bill.ts>=strtotime('2006-07-01') && $bill.comment!="разбивка Markomnet")*}
<b>Поставщик: ООО &laquo;Эм Си Эн&raquo;</b><br>
ИНН:&nbsp;7727508671 &nbsp;&nbsp;КПП:&nbsp;772701001<br>
Адрес:&nbsp;113452, г.Москва, Балаклавский пр-т, д.20, кор. 4 кв. 130<br>
Телефон: (495) 950-5678<br>
<!--Факс: (495) 335-3723<br>-->
<!--Факс: (495) 638-5184, 137-2674<br>-->
Факс: (495) 638-50-17<br>
р/с:&nbsp;40702810600301422002 в ЗАО КБ &laquo;Ситибанк&raquo;<br>
к/с:&nbsp;30101810300000000202<br>
БИК:&nbsp;044525202<br>
{else}
<b>Поставщик: {$firm.name}</b><br>
ИНН:&nbsp;{$firm.inn};&nbsp;&nbsp;КПП:&nbsp;{$firm.kpp}<br>
Адрес:&nbsp;{$firm.address}<br>
{if isset($firm.phone)}Телефон: {$firm.phone}<br>{/if}
{if isset($firm.fax)}Факс: (495) 638-50-17<br>{/if}
р/с:&nbsp;{$firm.acc} в {$firm.bank}<br>
к/с:&nbsp;{$firm.kor_acc}<br>
БИК:&nbsp;{$firm.bik}<br>
{*
<b>Поставщик: ООО &laquo;МАРКОМНЕТ&raquo;</b><br>
ИНН:&nbsp;7734246040;&nbsp;&nbsp;КПП:&nbsp;773401001<br>
Адрес:&nbsp;123458, г.Москва, Таллинская ул., д.2/282<br>
Телефон: (495) 335-0893<br>
<!--Факс: (495) 335-3723<br>-->
<!--Факс: (495) 638-5184, 137-2674<br>-->
Факс: (495) 638-50-17<br>
р/с:&nbsp;40702810300070015232 в АКБ &laquo;Пробизнесбанк&raquo; (ОАО)<br>
к/с:&nbsp;30101810600000000986<br>
БИК:&nbsp;044525986<br>
*}
{/if}</p>

</td>
<td align=right>
<div style="width: 110px;  text-align: center;padding-right: 10px;">
{if $bill_client.firma == "all4net"}
<img border="0" src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}logo_all4net.gif">
{elseif $bill_client.firma == "mcn"}
<img border="0" src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}logo2.gif">
{elseif $bill_client.firma == "mcn_telekom"}
<img border="0" src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}mcntelecom-logo.png"  width=115 height=25 style="">
www.mcntelecom.ru
{/if}
</div>

  {if $bill_client.client != "salvus"}
    <table border="0" align="right">
      <tr {if $bill.sum > 300} bgcolor="#FFD6D6"{/if}>
        <td>Клиент</td>
        <td>{$bill_client.client}</td>
      </tr>
      {assign var="color" value=""}
      {if $bill_client.manager eq 'bnv'}{assign var="color" value="#EEDCA9"}{/if}
      {if $bill_client.manager eq 'pma'}{assign var="color" value="#BEFFFE"}{/if}
      <tr valign=top bgcolor="{$color}">
        <td>Менеджер</td>
        <td width="50">{$bill_client.manager_name|replace:" ":"&nbsp;"}</td>
      </tr>
      <tr>
        <td colspan=2 align="center">{if $bill_no_qr}<img src="{if $is_pdf == '1'}{$WEB_PATH}{else}./{/if}utils/qr-code/get?data={$bill_no_qr.bill}">{else}&nbsp;{/if}</td>
      </tr>
    </table>
  {/if}
</td>
</tr>
</table>
<hr>


<center><h2>Счёт &#8470;{$bill.bill_no}</h2></center>



<p align=right>Дата: <b> {$bill.ts|mdate:"d.m.Y г."} </b></p>

<hr>
<br>
<p><b>Плательщик: {if $bill_client.head_company}{$bill_client.head_company}, {/if}{$bill_client.company_full}</b></p>

    {assign var=isDiscount value=0}
{foreach from=$bill_lines item=line key=key}{assign var=key value=$key+1}
    {assign var=isDiscount value=`$isDiscount+$line.discount_auto+$line.discount_set`}
{/foreach}


<table border="1" width="100%" cellspacing="0" cellpadding="2" style="font-size: 15px;">
  <tbody>
<tr>
    <td align="center"><b> п/п</b></td>
    <td align="center"><b>Предмет счета</b></td>
    <td align="center"><b>Количество</b></td>
    <td align="center"><b>Единица измерения</b></td>
    <td align="center"><b>Стоимость,&nbsp;{''|money:$curr}</b></td>
    <td align="center"><b>Сумма,&nbsp;{''|money:$curr}</b></td>
    <td align="center"><b>Сумма налога,&nbsp;{''|money:$curr}</b></td>
    <td align="center"><b>Сумма с учётом налога,&nbsp;{''|money:$curr}</b></td>
{if $isDiscount}
    <td align="center"><b>Скидка</b></td>
    <td align="center"><b>Сумма со скидкой,<br>с учётом налога,&nbsp;{''|money:$curr}</b></td>
{/if}
</tr>

{assign var=key value=0}


{foreach from=$bill_lines item=line key=key}{assign var=key value=$key+1}

{assign var=discount value=`$line.discount_auto+$line.discount_set`}
<tr>
		<td align="right">{$key}</td>
		<td>{$line.item}</td>
		<td align="center">{$line.amount|mround:4:6}</td>
        <td align="center">{if $line.okvd_code}{$line.okvd_code|okei_name}{else}{if $line.type == "service"}-{else}шт.{/if}{/if}</td>
        <td align="center">{$line.outprice|round:4}</td>
        <td align="center">{$line.sum_without_tax|round:2}</td>
        <td align="center">{if $bill_client.nds_zero || $line.line_nds == 0}без НДС{else}{$line.sum_tax|round:2}{/if}</td>
        <td align="center">{$line.sum|round:2}</td>
        {if $isDiscount}
            <td align="center">{$discount|round:2}</td>
        {assign var=line_sum value=`$line.sum-$discount`}
            <td align="center">{$line_sum|round:2}</td>
        {/if}
</tr>
{/foreach}

<tr>
    <td colspan="5">
	<p align="right"><b>Итого:</b></p></td>
	<td align="center">{$bill.sum_without_tax|round:2}</td>
    <td align="center">
        {if !$isDiscount}
            {if $bill_client.nds_zero}без НДС{else}{$bill.sum_tax|round:2}{/if}
        {else}
            &nbsp;
        {/if}
    </td>
        {if $isDiscount}
            <td align="center">&nbsp;</td>
            <td align="center">{$isDiscount|round:2}</td>
        {/if}
    <td align="center">{$bill.sum-$isDiscount|round:2}</td>
</tr>

</tbody></table>
<br>
<p><i>Сумма прописью:  {$bill.sum-$isDiscount|wordify:'RUB'}</i></p>

<table border="0" align=center cellspacing="1" cellpadding="0"><tbody>
<tr>
	<td>{$firm_director.position}</td>
{if isset($emailed) && $emailed==1}
    <td>
        {if $firm_director.sign} <img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_director.sign.src}"  border="0" alt="" align="top"{if $firm_director.sign.width} width="{$firm_director.sign.width}" height="{$firm_director.sign.height}"{/if}> {else} _________________________________ {/if}
    </td>

{else}
	<td><br><br>_________________________________<br><br></td>
{/if}
	<td>/ {$firm_director.name} /</td>
</tr><tr>
	<td>Главный бухгалтер</td>
{if isset($emailed) && $emailed==1}
	<td>
            {if $firm_buh.sign}<img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_buh.sign.src}"  border="0" alt="" align="top"{if $firm_buh.sign.width} width="{$firm_buh.sign.width}" height="{$firm_buh.sign.height}"{/if}>{else} _________________________________ {/if}

    </td>
{else}
	<td><br><br>_________________________________<br><br></td>
{/if}
	<td>
    / {$firm_buh.name} /
        </td>
</tr>
{if isset($emailed) && $emailed==1}<tr>
	<td>&nbsp;</td>
	<td align=left>

{if $firma}<img style='{$firma.style}' src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firma.src}"{if $firma.width} width="{$firma.width}" height="{$firma.height}"{/if}>{/if}

    </td>
	<td>&nbsp;</td>
</tr>{/if}
</tbody></table>

{if $bill_client.firma != 'ooocmc' && $bill_client.firma != 'ooomcn'}
<small>
Примечание:
При отсутствии оплаты счета до конца текущего месяца услуги по договору будут приостановлены до полного погашения задолженности.
</small>
{/if}
</body>
</html>
