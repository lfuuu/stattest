<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
<LINK href="{if $is_pdf == '1'}{$WEB_PATH}{else}{$PATH_TO_ROOT}{/if}print.css" type=text/css rel=stylesheet>
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">

<h2 align=center>АКТ ПРИЕМА ПЕРЕДАЧИ {*<br>N {$bill.bill_no}{$inv_no} от {$inv_date|mdate:"d.m.Y г."}*}</h2>
<h3 align=center>по&nbsp;договору &#8470; {$contract.contract_no}{if !$without_date_date} от {$contract.contract_date|mdate:"d.m.Y г."}{else} от {$without_date_date|mdate:"d.m.Y г."}{/if}</h3>
<table align=center width=90%><tr><td align=left>г. Москва </td><td align=right><b>{if isset($cpe[0])}{$cpe[0].actual_from|mdate:"d.m.Y г."}{else}{php} echo date("d.m.Y г.");{/php}{/if}</b></td></tr></table>
<br>
<br>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     Мы, нижеподписавшиеся, 

{$firm_director.position} {$firm_director.name} {$firma.name}
	 и представитель <b>{$bill_client.company_full}</b>, в&nbsp;лице <b>{$bill_client.signer_positionV} {$bill_client.signer_nameV}</b>, произвели прием-передачу во временное пользование следующего оборудования:
</p><ul>

{foreach from=$cpe item=r}
		 <li>{if $r.type && $r.vendor}{$r.type|upper}-модем {$r.vendor} {/if}{if $r.model eq 'Залог за модем'}Модем{else}{$r.model}{/if}{if $r.serial}, серийный номер {$r.serial|upper}{/if}, {$r.amount} шт.</li>
{/foreach}
	 </ul><p>Получен залог в сумме {$bill.sum|round:2} рублей</p>
	 <p>&nbsp;</p>
	 <p>&nbsp;</p>
	 <p>&nbsp;</p>
{if $to_client == "true"}
    <b>Обращаем Ваше Вниманиние!</b> <br>Этот экземпляр Акта, просьба с подписью и печатью направить в наш адрес:<br>115162 г.Москва,а/я 21 ООО "ЭмСиЭн"{/if}
	 <p>&nbsp;</p>

<TABLE cellSpacing=0 cellPadding=0 border=0 width="100%"><TBODY><TR><TD>
	Оператор: <b>{$firma.name}</b>
</td><td>
	Абонент: <b>{$bill_client.company_full}</b>
</td></tr><tr><td>
	<br><br><p>
{$firm_director.position} {if $firm_director.sign && isset($emailed) && $emailed==1} <img src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firm_director.sign.src}"  border="0" alt="" align="top" valign="middle"{if $firm_director.sign.width} width="{$firm_director.sign.width}" height="{$firm_director.sign.height}"{/if}> {else} ___________________ {/if}{$firm_director.name}
</td><td>
	<br><br><p>{$bill_client.signer_position}________{$bill_client.signer_name|replace:" ":"&nbsp;"}</p>
</td></TR></TBODY></TABLE>
{if isset($emailed) && $emailed==1}<tr>
	<div style="position: relative; top: 60;left: 240px;">
{if $firma}<img style='{$firma.style}' src="{if $is_pdf == '1'}{$WEB_PATH}images/{else}{$IMAGES_PATH}{/if}{$firma.src}"{if $firma.width} width="{$firma.width}" height="{$firma.height}"{/if}>{/if}
</div>{/if}
</body></html>
