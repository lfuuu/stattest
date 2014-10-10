<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=UTF-8">
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">
<center>
<h2>АКТ &#8470; 3-{$client.id}</h2>
<h3>СДАЧИ-ПРИЕМКИ ОБОРУДОВАНИЯ
</h3>
<table align=center width=90%><tr><td align=left>г. Москва </td><td align=right>"__" ___________ {*if $cpe.actual_from<=date('Y-m-d')}<b>{$cpe.actual_from|mdate:"Y"}</b>{else*}{php}echo date("Y");{/php}{*/if*} г.</td></tr></table>
</center>
<p>Настоящий акт составлен между Абонентом <b>{$client.company_full}</b>, в&nbsp;лице 
<b>{$client.signer_positionV} {$client.signer_nameV}</b> и&nbsp;Оператором 
{$firma.name} в&nbsp;лице {$firm_director.position_} {$firm_director.name_} о
том, что:</p>
<ol>
<li>
Абонент возвратил Оператору следующее оборудование:
<ul>
	<li>модель: {$cpe.vendor|upper} {$cpe.model|upper}</li>
	<li>тип: {$cpe.type|upper}</li>
	<li>серийный номер: {$cpe.serial}</li>
</ul><br>
{if false}для доступа в телефонную сеть общего пользования.<br><br>
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
{/if}
<TABLE cellSpacing=0 cellPadding=0 border=0 width="100%"><TBODY>
<TR>
<TD>
Оператор: {$firma.name} </td><td>Абонент: <b>{$client.company_full}</b></td>

</tr>
<tr><td>
<br><br>{$firm_director.position} ___________ /{$firm_director.name}/ </td><td>
<br><br>
{$client.signer_position}_____________ <nobr>{$client.signer_name}</nobr>
</td></TR></TBODY></TABLE>
</body></html>
