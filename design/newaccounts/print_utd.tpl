
<html>

<head>
<meta http-equiv=Content-Type content="text/html; charset=koi8-r">

<title>С/Ф АКТ N {$bill.bill_no}{$inv_no} от {$inv_date|mdate:"d.m.Y г."}</title>


<style>
{literal}
@page {size: landscape;}
@page rotated {size: landscape;}
div.Section1
	{page:Section1;
	align:center;}

.tr_h15 {height:15.0pt;}
.tr_h15 td {height:15.0pt;padding:0cm 5.4pt 0cm 5.4pt;}
.tr_h15 td p span{font-size:7.0pt;font-family:Arial;}

.tr_h11 {height:11.85pt;}
.tr_h11 td {height:11.85pt;padding:0cm 5.4pt 0cm 5.4pt;}
.tr_h11 td p span{font-size:8.0pt;font-family:Arial;}

.tr_h2 {height:1.95pt;}
.tr_h2 td {height:1.95pt;padding:0cm 5.4pt 0cm 5.4pt;}
.tr_h2 td p span{font-size:1.0pt;font-family:Arial;}

.tr_h8 {height:8.1pt;}
.tr_h8 td {height:8.1pt;padding:0cm 5.4pt 0cm 5.4pt;}
.tr_h8 td p span{font-size:6.0pt;font-family:Arial;}

.tr_h20 {height:20.65pt;}
.tr_h20 td {height:20.65pt;padding:0cm 5.4pt 0cm 5.4pt;}
.tr_h20 td p span{font-size:7.0pt;font-family:Arial;}

.tr_h30 {height:28.4pt;}
.tr_h30 td {height:30.4pt;padding:0cm 5.4pt 0cm 5.4pt;}
.tr_h30 td p span{font-size:7.0pt;font-family:Arial;}

.tr_h50 {height:28.4pt;}
.tr_h50 td {height:50.4pt;padding:0cm 5.4pt 0cm 5.4pt;}
.tr_h50 td p span{font-size:7.0pt;font-family:Arial;}

{/literal}
</style>

</head>

<body lang=RU style='tab-interval:35.4pt'>

<div align="center"><center>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='margin-left:4.65pt;border-collapse:collapse;'>
	<tr class='tr_h15'>
		<td rowspan=3 valign=bottom style='width:77pt;'>
			<p class=MsoNormal><span>Универсальный передаточный<br>документ</span></p>
		</td>
		<td rowspan="3" valign=bottom style='border-left: solid windowtext 1.0pt;padding:0cm 2.4pt 0cm 2.4pt;'>
			<p class=MsoNormal><span style='font-size:1.0pt;'>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:108pt;'>
			<p class=MsoNormal><span>С/Ф Акт  N</span></p>
		</td>
		<td valign=bottom style='width:120pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>{$bill.bill_no}{$inv_no}</span></p>
		</td>
		<td valign=bottom style='width:20pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>от</span></p>
		</td>
		<td valign=bottom style='width:120pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>
                {if !$without_date_date}
                    {if $is_four_order && isset($inv_pays)}
                        {$inv_pays[0].payment_date_ts|mdate:"d месяца Y г."}
                    {else}
                        {$inv_date|mdate:"d месяца Y г."}
                    {/if}
                {else} 
                    {$without_date_date|mdate:"d месяца Y г."}
                {/if}</span></p>
		</td>
		<td valign=bottom style='width:20pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>(1)</span></p>
		</td>
		<td rowspan=3 valign=top style='width:420pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span style='font-size:6.0pt;'>Приложение N 1<br>
				к постановлению Правительства Российской Федерации<br>от 26 декабря 2011 г. N 1137</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td valign=bottom>
			<p class=MsoNormal><span>Исправление N</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>--</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:center'><span>от</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>--</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:center'><span>(1а)</span></p>
		</td>
	</tr>
	<tr style='height:3.45pt;'>
		<td colspan="6" valign=bottom style='padding:0cm 5.4pt 0cm 5.4pt;height:3.45pt'>
			<p class=MsoNormal><span style='font-size:4.0pt;'>&nbsp;</span></p>
		</td>
	</tr>
</table>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='margin-left:4.65pt;border-collapse:collapse;'>
	<tr class='tr_h11'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td rowspan="11" valign=bottom style='border-left: solid windowtext 1.0pt;padding:0cm 2.4pt 0cm 2.4pt;'>
			<p class=MsoNormal><span style='font-size:1.0pt;'>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:188pt;'>
			<p class=MsoNormal><b><span>Продавец:</span></b></p>
		</td>
		<td valign=bottom style='width:700pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$firm.name}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(2)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td style='width:42pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Статус:</span></p>
		</td>
		<td style='width:8pt;border:solid windowtext 1.0pt;' >
			<p class=MsoNormal align=center style='text-align:center'><span>1</span></p>
		</td>
		<td valign=top style='width:6pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal><span>Адрес:</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$firm.address}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(2а)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal><span>ИНН/КПП продавца:</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$firm.inn}&nbsp;/&nbsp;{$firm.kpp}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(2б)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td colspan=3 rowspan=5 valign=top>
			<p class=MsoNormal><span style='font-size:6.0pt;'>1 - счет-фактура и передаточный документ (акт)<br> 2 - передаточный документ (акт)</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>Грузоотправитель и его адрес:</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}&nbsp;--{else}ООО "МСН Телеком"{$firm.address}{/if}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(3)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td valign=bottom>
			<p class=MsoNormal><span>Грузополучатель и его адрес:</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if (('2009-06-01' < $bill.bill_date || ($bill.bill_date eq '2009-06-01' && $invoice_source <> 2)) && $invoice_source <> 3) || $is_four_order}&nbsp;--{else}{$bill_client.company_full}{$bill_client.address_post}{/if}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(4)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td valign=bottom>
			<p class=MsoNormal><span>К платежно-расчетному документу N</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if isset($inv_pays)} {foreach from=$inv_pays item=inv_pay name=outer}N{$inv_pay.payment_no} от {$inv_pay.payment_date_ts|mdate:"d.m.Y г."}{if !$smarty.foreach.outer.last}, {/if}{/foreach}{/if}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(5)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td valign=bottom>
			<p class=MsoNormal><span><b>Покупатель:</b></span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(6)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td valign=top>
			<p class=MsoNormal><span>Адрес:</span></p>
		</td>
		<td valign=top style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if $bill_client.head_company_address_jur}{$bill_client.head_company_address_jur}{else}{$bill_client.address_jur}{/if}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(6а)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal><span>ИНН/КПП покупателя:</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$bill_client.inn}&nbsp;/{$bill_client.kpp}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(6б)</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>Валюта: наименование, код</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>Российский рубль, 643</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:left'><span>(7)</span></p>
		</td>
	</tr>
	<tr class='tr_h2'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
</table>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='margin-left:4.65pt;border-collapse:collapse;'>
	<tr class='tr_h30'>
		<td rowspan=2 style='width:22pt;border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>N<br>п/п</span></p>
		</td>
		<td rowspan=2 style='width:42pt;border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Код товара/работ, услуг</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;mso-border-left-alt: solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Наименование товара (описание выполненных работ, оказанных услуг), имущественного права</span></p>
		</td>
		<td colspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Единица<br> измерения</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Коли-<br>чество <br> (объем)</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Цена (тариф)<br>за<br>единицу измерения</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Стоимость товаров (работ, услуг), имущест-<br>венных прав без налога - всего</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>В том<br>числе<br>сумма <br>акциза</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Нало-<br>говая ставка</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Сумма налога, предъяв-<br>ляемая покупателю</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Стоимость товаров (работ, услуг), имущест-<br> венных прав с налогом - всего</span></p>
		</td>
		<td colspan="2" style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Страна<br> происхождения товара</span></p>
		</td>
		<td rowspan=2 style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>Номер<br> таможенной<br> декларации</span></p>
		</td>
	</tr>
	<tr class='tr_h50'>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>код</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>условное обозна-<br>чение (нацио-<br>нальное)</span></p>
		</td>
		<td style='border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>циф-<br>ро-<br>вой код</span></p>
		</td>
		<td style='border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>краткое наиме-<br>нование</span></p>
		</td>
	</tr>
	<tr class='tr_h11'>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>А</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>Б</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>1</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>2</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>2а</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>3</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>4</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>5</span></p>
		</td>
		<td style='width:34pt;border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;' >
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>6</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;' >
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>7</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;' >
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>8</span></p>
		</td>
		<td style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>9</span></p>
		</td>
		<td style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>10</span></p>
		</td>
		<td style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>10а</span></p>
		</td>
		<td style='border:solid windowtext 1.0pt;' >
			<p class=MsoNormal align=center style='text-align:center'><span style='font-size:6.0pt;'>11</span></p>
		</td>
	</tr>
{foreach from=$bill_lines item=row key=key name='list'}
	<tr class='tr_h15'>
		<td valign=top style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>{$smarty.foreach.list.iteration}</span></p>
		</td>
		<td valign=top style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$row.item}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>
                {if $is_four_order}
                --
                {else}
                    {if $inv_is_new4}
                        {if $row.okvd_code}
                            {$row.okvd_code|string_format:"%03d"}
                        {else}
                            {if $row.type == "service"}
                            --
                            {else}
                            769
                            {/if}
                        {/if}
                    {else}
                    769
                    {/if}
                {/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>
            {if $is_four_order}
                --
            {else}
                {if $inv_is_new4}
                    {if $row.okvd_code}
                        {$row.okvd}
                    {else}
                        {if $row.type == "service"}
                            --
                        {else}
                            шт.
                        {/if}
                    {/if}
                {else}
                    шт.
                {/if}
            {/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>
        {if $bill.bill_date < "2012-01-01"}{$row.amount|round:2}{else}
            {if $is_four_order}
                --
            {else}
                {if $inv_is_new4}
                    {if $row.okvd_code}
                        {$row.amount|round:2}
                    {else}

                        {if $row.type == "service"}
                            --
                        {else}
                            {$row.amount|round:2}
                        {/if}
                    {/if}
                {else}
                    {$row.amount|round:2}
                {/if}
            {/if}
        {/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>
            {if $is_four_order}
                --
            {else}
                {if $inv_is_new4}
                    {if $row.okvd_code}
                        {$row.outprice|round:2}
                    {else}
                        {if $row.type == "service"}
                            --
                        {else}
                            {$row.outprice|round:2}
                        {/if}
                    {/if}
                {else}
                    {$row.outprice|round:2}
                {/if}
            {/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>
            {if $is_four_order}
                --
            {else}
                {if $bill_client.nds_calc_method != 1}
                    {$row.sum|mround:2:2}
                {else}
                    {$row.sum|mround:2:2}
                {/if}
                    
            {/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if $inv_is_new4}без акциза{else}--{/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if $row.tax == 0}без НДС{else}{if $is_four_order eq true}18%/118%{else}18%{/if}{/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>
            {if $bill_client.nds_calc_method != 1}
                {$row.tax|string_format:"%.2f"}
            {else}
                {if $row.tax == 0 && $row.line_nds == 0}
                    --
                {else}
                    {$row.tsum/1.18*0.18|round:2}
                {/if}
            {/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>
            {if $bill_client.nds_calc_method != 1}
                {$row.tsum|round:2}
            {else}
                {$row.tsum|round:2}
            {/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if $row.country_id == 0}--{else}{$row.country_id}{/if}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$row.country_name|default:"--"}</span></p>
		</td>
		<td valign=top style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$row.gtd|default:"--"}</span></p>
		</td>
	</tr>
{/foreach}
	<tr class='tr_h11'>
		<td colspan="2" valign=bottom style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>&nbsp;</span></p>
		</td>
		<td colspan=5 valign=bottom style='border-left:solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><b><span style='font-size:8.0pt;font-family:Arial'>Всего к оплате</span></b></p>
		</td>
		<td valign=bottom style='border:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>{if $is_four_order}--{else}{$bill.sum|round:2}{/if}</span></p>
		</td>
		<td colspan=2 valign=bottom style='border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span><b>Х</b></span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>
            {if $bill_client.nds_calc_method != 1}
                {$bill.tax|string_format:"%.2f"}
            {else}
                {if $bill.tax == 0 && $bill.sum}
                    --
                {else}
                    {$bill.tsum/1.18*0.18|round:2}
                {/if}
            {/if}</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=right style='text-align:right'><span>{$bill.tsum|round:2}</span></p>
		</td>
		<td colspan="3" valign=bottom style='border-bottom:solid windowtext 1.0pt;border-right:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
</table>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='margin-left:4.65pt;border-collapse:collapse;'>
	<tr class='tr_h20'>
		<td rowspan=3 valign=top>
			<p class=MsoNormal><span>Документ<br>составлен на<br>1 листе</span></p>
		</td>
		<td rowspan="4" style='border-left: solid windowtext 1.0pt;border-bottom:solid windowtext 1.0pt;padding:0cm 2.4pt 0cm 2.4pt;'>
			<p class=MsoNormal><span style='font-size:1.0pt;'>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:170pt;'>
			<p class=MsoNormal><span>Руководитель организации<br>или иное уполномоченное лицо</span></p>
		</td>
		<td valign=bottom style='width:80pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style="width:20pt">
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:80pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$firm_director.name}</span></p>
		</td>
		<td valign=bottom style="width:20pt">
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:170pt;'>
			<p class=MsoNormal><span>Главный бухгалтер<br>или иное уполномоченное лицо</span></p>
		</td>
		<td valign=bottom style='width:80pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style="width:20pt">
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:80pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$firm_buh.name}</span></p>
		</td>
		<td valign=bottom style='width:20pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
	<tr class='tr_h8'>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(подпись)</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(ф.и.о.)</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(подпись)</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td colspan=4 valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(ф.и.о.)</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td valign=bottom>
			<p class=MsoNormal><span style="font-size:7pt;">Индивидуальный предприниматель</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td colspan="5" valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>&nbsp;</span></p>
		</td>
	</tr>
	<tr class='tr_h8'>
		<td valign=bottom style='width:76pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>(подпись)</span></p>
		</td>
		<td valign=bottom style='border-bottom: solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>(ф.и.о.)</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td colspan=5 valign=top style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal align=center style='text-align:center'><span>(реквизиты свидетельства о государственной регистрации индивидуального предпринимателя)</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>Основание передачи (сдачи) / получения (приемки)</span></p>
		</td>
		<td colspan="8" valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[8]</span></p>
		</td>
	</tr>
	<tr class='tr_h8'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td colspan="8" valign=bottom>
			<p class=MsoNormal align=center style='text-align:center'><span>(договор; доверенность и др.)</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>Данные о транспортировке и грузе</span></p>
		</td>
		<td colspan="8" valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[9]</span></p>
		</td>
	</tr>
	<tr class='tr_h8'>
		<td colspan="3" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td colspan="8" valign=bottom>
			<p class=MsoNormal align=center style='text-align:center'><span>(транспортная накладная, поручение экспедитору, экспедиторская / складская расписка и др. / масса нетто/ брутто груза, если не приведены ссылки на транспортные документы, содержащие эти сведения)</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
</table>

<table class=MsoNormalTable border=0 cellspacing=0 cellpadding=0 style='margin-left:4.65pt;border-collapse:collapse;'>
	<tr class='tr_h15'>
		<td colspan="6" valign=bottom>
			<p class=MsoNormal><span>Товар (груз) передал / услуги, результаты работ, права сдал</span></p>
		</td>
		<td rowspan="14" style='width:7.85pt;border-left: solid windowtext 1.0pt;'>
			<p class=MsoNormal><span style='font-size:1.0pt;'>&nbsp;</span></p>
		</td>
		<td colspan="6" valign=bottom>
			<p class=MsoNormal><span>Товар (груз) получил / услуги, результаты работ, права принял</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td valign=bottom style='width:125pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>Генеральный директор</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:125pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:125pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$firm_director.name}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[10]</span></p>
		</td>
		<td valign=bottom style='width:125pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:125pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:125pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[15]</span></p>
		</td>
	</tr>
	<tr class='tr_h8'>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(должность)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(подпись)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(ф.и.о.)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(должность)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(подпись)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(ф.и.о.)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td valign=bottom>
			<p class=MsoNormal><span>Дата отгрузки, передачи (сдачи)</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>
                {if !$without_date_date}
                    {if $is_four_order && isset($inv_pays)}
                        {$inv_pays[0].payment_date_ts|mdate:"d месяца Y года"}
                    {else}
                        {$inv_date|mdate:"d месяца Y года"}
                    {/if}
                {else} 
                    {$without_date_date|mdate:"d месяца Y года"}
                {/if}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[11]</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>Дата получения (приемки)</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>"{section loop="6" name="mysec"}&nbsp;{/section}"{section loop="20" name="mysec"}&nbsp;{/section}20{section loop="10" name="mysec"}&nbsp;{/section}года</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[16]</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td colspan="6" valign=bottom>
			<p class=MsoNormal><span>Иные сведения об отгрузке, передаче</span></p>
		</td>
		<td colspan="6" valign=bottom>
			<p class=MsoNormal><span>Иные сведения о получении, приемке</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td colspan="5" valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='border:none;'>
			<p class=MsoNormal><span>[12]</span></p>
		</td>
		<td colspan="5" valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[17]</span></p>
		</td>
	</tr>
	<tr class='tr_h8'>
		<td colspan="5" valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(ссылки на неотъемлемые приложения, сопутствующие документы, иные документы и т.п.)</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td colspan="5" valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(информация о наличии/отсутствии претензии; ссылки на неотъемлемые приложения, и другие документы и т.п.)</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td colspan="6" valign=bottom>
			<p class=MsoNormal><span>Ответственный за правильность оформления факта хозяйственной жизни</span></p>
		</td>
		<td colspan="6" valign=bottom>
			<p class=MsoNormal><span>Ответственный за правильность оформления факта хозяйственной жизни</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td valign=bottom style='width:100pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:100pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:100pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[13]</span></p>
		</td>
		<td valign=bottom style='width:100pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:100pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom style='width:100pt;border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[18]</span></p>
		</td>
	</tr>
	<tr class='tr_h8'>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(должность)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(подпись)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(ф.и.о.)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(должность)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(подпись)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(ф.и.о.)</span></p>
		</td>
		<td>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td colspan="6" valign=bottom>
			<p class=MsoNormal><span>Наименование экономического субъекта - составителя документа (в т.ч. комиссионера / агента)</span></p>
		</td>
		<td colspan="6" valign=bottom>
			<p class=MsoNormal><span>Наименование экономического субъекта - составителя документа</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td colspan="5" valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{$firm.name}, ИНН/КПП {$firm.inn}&nbsp;/&nbsp;{$firm.kpp}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[14]</span></p>
		</td>
		<td colspan="5" valign=bottom style='border-bottom:solid windowtext 1.0pt;'>
			<p class=MsoNormal><span>{if $bill_client.head_company}{$bill_client.head_company}{else}{$bill_client.company_full}{/if}, ИНН/КПП {$bill_client.inn}&nbsp;/&nbsp;{$bill_client.kpp}</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal><span>[19]</span></p>
		</td>
	</tr>
	<tr class='tr_h8'>
		<td colspan="5" valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td colspan="5" valign=top>
			<p class=MsoNormal align=center style='text-align:center'><span>(может не заполняться при проставлении печати в М.П., может быть указан ИНН / КПП)</span></p>
		</td>
		<td valign=top>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
	<tr class='tr_h15'>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:center'><span>МП</span></p>
		</td>
		<td colspan="5" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
		<td valign=bottom>
			<p class=MsoNormal align=center style='text-align:center'><span>МП</span></p>
		</td>
		<td colspan="5" valign=bottom>
			<p class=MsoNormal><span>&nbsp;</span></p>
		</td>
	</tr>
</table>
</center></div>
</body>
</html>
