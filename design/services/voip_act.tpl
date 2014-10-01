<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">
<center>
<h2>АКТ</h2>
<h3>сдачи-приемки работ<br>
по дополнительному соглашению к&nbsp;договору &#8470; {$contract.contract_no} от {$contract.contract_date}
<table align=center width=90%><tr><td align=left>г. Москва </td><td align=right>{if $conn.actual_from<=date('Y-m-d')}<b>{$conn.actual_from|mdate:"d.m.Y г."}</b>{else}&nbsp;{/if}</td></tr></table>
</h3></center>
<p>Настоящий акт составлен между Абонентом <b>{$client.company_full}</b>, в&nbsp;лице 
<b>{$client.signer_positionV} {$client.signer_nameV}</b>, и&nbsp;Оператором 
{if $client.firma eq "mcn"}
	ООО &laquo;Эм Си Эн&raquo;, в&nbsp;лице Генерального директора Мельникова&nbsp; А.&nbsp;К., о&nbsp;
{else}
	ООО &laquo;МАРКОМНЕТ&raquo; в&nbsp;лице директора Мельникова&nbsp; А.&nbsp;К., о&nbsp;
{/if} 
том, что:</p>

<ol>
{foreach from=$voip_connections item=item}{if $item.address}
<li>Абоненту был установлено подключение к&nbsp;телефонной сети общего пользования по&nbsp;адресу: <b>{$item.address}</b></li>
{/if}{/foreach}
<li>Абоненту выданы следующие телефонные номера:<br>
<ul>
{foreach from=$voip_connections item=item}
<li>(495) {$item.E164_last} {*if $item.no_of_lines>1} x {$item.no_of_lines}{/if*}</li>
{/foreach}
</ul>
</ol><br>

<p>Каналы связи проверены представителем Заказчика, функционируют нормально и&nbsp;удовлетворяют требованиям Договора.</p>

<h5>Информация для получения статистики</h5>
Страница просмотра: <i>https://lk.mcn.ru/</i><br>

Логин: <b>{$client.client}</b><br>
Пароль: <b>{$client.password}</b>
</p>


<TABLE cellSpacing=0 cellPadding=0 border=0 width="100%"><TBODY>
<TR>
<TD>
<p>Оператор: {if $client.firma eq "mcn"}
ООО &laquo;Эм Си Эн&raquo; &nbsp;{else}ООО &laquo;МАРКОМНЕТ&raquo;
{/if} </td><td>Абонент: <b>{$client.company_full}</b></td>


</tr><tr><td>
<br><br><p>{if $client.firma eq "mcn"}
Ген. директор {else}Директор{/if} ___________ Мельников&nbsp; А.&nbsp;К. </td><td>
{$client.signer_position}/_____________/{$client.signer_name}</p>
</td></TR></TBODY></TABLE>
</body></html>
