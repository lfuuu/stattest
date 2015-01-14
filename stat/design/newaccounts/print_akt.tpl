<html>

<head>
<LINK title=default href="{if $is_pdf == '1'}{$WEB_PATH}{else}{$PATH_TO_ROOT}{/if}invoice.css" type=text/css rel=stylesheet>
<title>Акт &#8470;{$bill.bill_no}{$inv_no} от {$inv_date|mdate:"d.m.Y г."}</title>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
</head>


<body bgcolor="#FFFFFF" text="#000000">

<table width=95%>
<tr>
  <td>

{if $bill_client.firma == 'mcn_telekom'}
{$bill.bill_no}{$inv_no}
{/if}
<br><br>
{if $to_client == "true" && $bill_client.firma == 'mcn_telekom'}
    <b>Обращаем Ваше Вниманиние!</b> Этот экземпляр Акта, просьба с подписью и печатью направить в наш адрес: 115162 г.Москва,а/я 21 ООО &laquo;МСН Телеком&raquo;<br><br>{/if}

{if $bill_client.firma eq 'all4geo'}
    <strong>ООО &laquo;Олфогео&raquo;</strong><br>
    <strong>ИНН/КПП 7727752091/772401001</strong>

{elseif $bill_client.firma eq 'all4net'}
<strong>ООО &laquo;Олфонет&raquo;</strong><br>
<strong>ИНН/КПП 7727731060/772701001</strong>

{elseif $bill_client.firma eq 'ooocmc'}
<strong>ООО &laquo;Си Эм Си&raquo;</strong><br>
<strong>ИНН/КПП 7727701308/772701001</strong>

{elseif $bill_client.firma eq 'mcn_telekom'}
<strong>{$firm.name}</strong><br>
<strong>ИНН/КПП {$firm.inn}/{$firm.kpp}</strong>

{if false}<!--strong>ООО &laquo;МСН Телеком&raquo;</strong><br>
<strong>ИНН/КПП 7727752084/772401001</strong-->{/if}

{elseif $bill_client.firma eq 'markomnet_service'}
<strong>ООО &laquo;Маркомнет сервис&raquo;</strong><br>
<strong>ИНН/КПП 7728802130/772801001</strong>

{elseif $bill_client.firma eq 'mcm'}
<strong>ООО &laquo;МСМ&raquo;</strong><br>
<strong>ИНН/КПП 7727667833/772701001</strong>

{elseif $bill_client.firma eq 'ooomcn'}
<strong>ООО &laquo;МСН&raquo;</strong><br>
<strong>ИНН/КПП 7728638151/772801001</strong>

{elseif $bill_client.firma == 'markomnet'}
<strong>ООО "МАРКОМНЕТ"</strong><br>
<strong>123458, г. Москва, Таллинская ул., д.2, кв. 282</strong><br>

{elseif $bill_client.firma == 'markomnet_new'}
<strong>ООО "МАРКОМНЕТ"</strong><br>
<strong>117218, г. Москва, Б.Черемушкинская ул., д.25, стр.97</strong><br>

{elseif $bill_client.firma=='mcn'}{* || ($bill_client.nal=='beznal' && $bill.ts>=strtotime('2006-07-01') && $bill.comment!="разбивка Markomnet")*}
<strong>OOO "Эм Си Эн"</strong><br>
<strong>ИНН/КПП 7727508671/772701001</strong><br>
<!-- strong>113452 г. Москва, Балаклавский пр-т., д. 20, кор. 4 кв. 130</strong><br-->

{else}
<strong>{$firm.name}</strong><br>
<strong>ИНН/КПП {$firm.inn}/{$firm.kpp}</strong><br>
{/if}

{if $bill_client.firma eq 'all4geo'}
    <br>
{elseif $bill_client.firma eq 'all4net'}
Телефон: <strong>(495) 638-77-77</strong><br>
{elseif $bill_client.firma eq 'markomnet_new'}
Телефон: <strong>(495) 638-638-4</strong><br>
{elseif $bill_client.firma eq 'mcn_telekom'}
Телефон: <strong>{$firm.phone}</strong><br>
{if false}<!-- Телефон: <strong>(495) 950-56-78</strong><br-->{/if}
{elseif $bill_client.firma eq 'markomnet_service'}
Телефон: <strong>(495) 638-63-84</strong><br>
{elseif $bill_client.firma eq 'mcm'}
Телефон: <strong>(495) 950-58-41</strong><br>
{else}
Телефон: <strong>(495) 950-56-78</strong><br>
{/if}
<br>
Заказчик: <strong style="font-size: 10pt;">{if $bill_client.head_company}{$bill_client.head_company}, {/if}{$bill_client.company_full}</strong>
</td> {if $bill_no_qr}
  <td align="right"><br><img src="{if $is_pdf == '1'}{$WEB_PATH}{else}./{/if}utils/qr-code/get?data={$bill_no_qr.akt[$source]}"></td>
{/if}
  </tr>
  </table>

    <br>

<div align="center">
<h2>
Акт &#8470;{$bill.bill_no}{$inv_no}{if !$without_date_date} от {$inv_date|mdate:"d.m.Y г."}{else} от {$without_date_date|mdate:"d.m.Y г."}{/if}
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
{foreach from=$bill_lines item=line key=key}
      <tr>
        <td align="center">{$key+1}</td>
        <td>{$line.item}</td>
        <td align="center">

        {*if $inv_is_new4 && $line.type == "service"}-{else}ЫФ.{/if*}

                {if $inv_is_new4}
                    {if $line.okvd_code}
                        {$line.okvd_code|okei_name}
                    {else}
                        {if $line.type == "service"}
                            -
                        {else}
                            шт.
                        {/if}
                    {/if}
                {else}
                   шт.
                {/if}

{*if $line.okvd_code}{$line.okvd}{else}{if $line.type == "service"}-{else}шт.{/if}{/if*}</td>
        <td align="center">{$line.amount|round:4}</td>
        <td align="center">{$line.outprice|round:4}</td>
        <td align="center">{$line.sum|mround:2:4}</td>
      </tr>
{/foreach}

      <tr>
        <td colspan=5 align="right"><b>Итого:</b></td>
        <td align="right">{$bill.sum|round:2}</td>
      </tr>
      <tr>
        <td colspan=5 align="right"><b>Итого НДС:</b></td>
        <td align="right">{if $bill_client.nds_zero}без НДС{else}{if $bill.tax == 0 && $bill.sum}0.00{else}{$bill.tax|round:2}{/if}{/if}{*$bill.tax|round:2*}</td>
      </tr>
      <tr>
        <td colspan=5 align="right"><b>Всего (с учетом НДС):</b></td>
        <td align="right">{$bill.tsum|round:2}</td>
      </tr>
    </table>
    </center></div>
    <br>
    Всего оказано услуг на сумму: {$bill.tsum|wordify:'RUR'}{if !$bill_client.nds_zero}<br>В т.ч. НДС: {$bill.tax|round:2|wordify:'RUR'}{else} (Без НДС){/if}<br>
    <br>
    Вышеперечисленные услуги выполнены полностью и в срок. Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.
    <br>
    <br>{if $to_client == "true" && $bill_client.firma == 'mcn'}
    <b>Обращаем Ваше Вниманиние!</b> <br>Этот экземпляр Акта, просьба с подписью и печатью направить в наш адрес:<br>115162 г.Москва,а/я 21 ООО"ЭмСиЭн"{/if}

    <br>
    <br>


    <div style="position:relative; top:{if isset($emailed) && $emailed==1}0{else}0{/if}px; z-index:10">
    <table border="0" cellpadding="0" cellspacing="5">
      <tr>
        <td><p>Исполнитель</td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td><p>Заказчик</td>
</tr>
<tr><td>
        <br><br><table border=0><tr><td>Руководитель организации</td>

            <td>{if isset($firm_director.sign) && $firm_director.sign && isset($emailed) && $emailed==1} <img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_director.sign.src}"  border="0" alt="" align="top"{if $firm_director.sign.width} width="{$firm_director.sign.width}" height="{$firm_director.sign.height}"{/if}> {else} _______________{/if}
            </td><td nowrap>
         / {$firm_director.name} /
         </td></tr></table>
              <br><br>
    </td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td nowrap>{$bill_client.signer_position}________________________/{$bill_client.signer_name|replace:" ":"&nbsp;"}/</td>
      </tr>
      <tr>
        <td align="center"><small>(подпись)</small></td>
    <td></td>
        <td align="center"><small>(подпись)</small></td>
      </tr>
      <tr>
        <td align="center"><br><br>М.П.</td>
    <td></td>
        <td align="center"><br><br>М.П.</td>
      </tr>
    </table>
    </div>
   </td>
  </tr>
</table>
{if isset($emailed) && $emailed==1}<tr>
	<div style="position: relative; top: -10;left: 100px;">
{if $firma && isset($firma.src) && $firma.src}<img style='{$firma.style}' src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firma.src}"{if $firma.width} width="{$firma.width}" height="{$firma.height}"{/if}>{/if}
</div>{/if}
</div>
</body>
</html>
