<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=koi8-r">
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">
<center>
<h2>АКТ</h2>
<h3>сдачи-приемки работ<br>
по дополнительному соглашению к&nbsp;договору &#8470; {$client.contract_no} от {$client.contract_date|mdate:'d.m.Y г.'}
</h3>
<table align=center width=90%><tr><td align=left>г. Москва </td><td align=right>{if $cpe.actual_from<=date('Y-m-d')}<b>{$cpe.actual_from|mdate:"d.m.Y г."}</b>{else}&nbsp;{/if}</td></tr></table>
</center>
<p>Настоящий акт составлен между Абонентом <b>{$client.company_full}</b>, в&nbsp;лице 
<b>{$client.signer_positionV} {$client.signer_nameV}</b> и&nbsp;Оператором 
{if $client.firma eq "mcn"}
ООО &laquo;Эм Си Эн&raquo; в&nbsp;лице Генерального директора Мельникова&nbsp; А.&nbsp;К. о&nbsp;
{else}ООО &laquo;МАРКОМНЕТ&raquo; в&nbsp;лице директора Мельникова&nbsp; А.&nbsp;К. о&nbsp;
{/if} 
том, что:</p>
<ol>
<li>
Абоненту был установлен VOIP-шлюз:
<ul>
	<li>модель: {$cpe.vendor|upper} {$cpe.model|upper}</li>
	<li>серийный номер: {$cpe.serial}</li>
</ul><br>
для доступа в телефонную сеть общего пользования.<br><br>
VOIP-шлюз установлен по адресу: {if $conn.address}{$conn.address}{else}{$client.address_jur}{/if}<br>
<br>
</li>
<li>На порты VOIP-шлюза назначены следующие телефонные номера:<br>
<ul>
{$cpe.numbers}
</ul>
</ol><br><br>

<p>Все вышеперечисленное оборудование передается Абоненту во&nbsp;временное пользование на&nbsp;срок действия договора.</p>
<p>Перечисленное выше оборудование, каналы связи проверены представителем Абонента, функционируют нормально и&nbsp;удовлетворяют требованиям договора.</p>

<TABLE cellSpacing=0 cellPadding=0 border=0 width="100%"><TBODY>
<TR>
<TD>
Оператор: {if $client.firma eq "mcn"}
ООО &laquo;Эм Си Эн&raquo; &nbsp;{else}ООО &laquo;МАРКОМНЕТ&raquo;
{/if} </td><td>Абонент: <b>{$client.company_full}</b></td>

</tr>
<tr><td>
<br><br>{if $client.firma eq "mcn"}
Генеральный директор {else}Директор{/if} ___________ Мельников&nbsp; А.&nbsp;К. </td><td>
<br><br>
{$client.signer_position}_____________{$client.signer_name}
</td></TR></TBODY></TABLE>
</body></html>
