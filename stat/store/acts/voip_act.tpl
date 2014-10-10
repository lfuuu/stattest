<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=UTF-8">
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">
<center>
<h2>АКТ &#8470; 4-{$client.id}</h2>
<h3>сдачи-приемки работ{if false}<br>
по дополнительному соглашению к&nbsp;договору &#8470; {$contract.contract_no} от {$contract.contract_date}{/if}
<table align=center width=90%><tr><td align=left>г. Москва </td><td align=right>"__" ____________ {*if $conn.actual_from<=date('Y-m-d')}<b>{$conn.actual_from|mdate:"Y"}</b>{else*}{php}echo date("Y");{/php}{*/if*} г.</td></tr></table>
</h3></center>
<p>Настоящий акт составлен между Абонентом <b>{$client.company_full}</b>, в&nbsp;лице 
<b>{$client.signer_positionV} {$client.signer_nameV}</b>, и&nbsp;Оператором 
{$firma.name}, в&nbsp;лице {$firm_director.position_} {$firm_director.name_}, о&nbsp;
том, что:</p>

<ol>
{*foreach from=$voip_connections item=item}{if $item.address}
<li>Абоненту был установлено подключение к&nbsp;телефонной сети общего пользования по&nbsp;адресу: <b>{$item.address}</b></li>
{/if}{/foreach*}
<li>Абоненту выданы следующие телефонные номера:<br>
<ul>
{foreach from=$voip_connections item=item}
<li>{if $item.E164_first}({$item.E164_first}) {/if}{$item.E164_last} {if $item.no_of_lines>1} x {$item.no_of_lines}{/if}{if $item.address} по&nbsp;адресу: {$item.address}{/if}</li>
{/foreach}
</ul>
</ol><br>

<p>Каналы связи проверены представителем Заказчика, функционируют нормально и&nbsp;удовлетворяют требованиям Договора.</p>

<h5>Информация для получения статистики</h5>
Страница просмотра: <i>https://lk.mcn.ru/</i><br>

{if $main_client}
Логин: <b>{$main_client.id}</b><br>
Пароль: <b>{$main_client.password}</b>
{else}
Логин: <b>{$client.id}</b><br>
Пароль: <b>{$client.password}</b>
{/if}
</p>


<TABLE cellSpacing=0 cellPadding=0 border=0 width="100%"><TBODY>
<tr>
	<td>
		<p>Оператор: {$firma.name}</td>
	<td>
		Абонент: <b>{$client.company_full}</b>
	</td>
</tr>
<tr>
	<td><br />
{$firm_director.position} ___________ / {$firm_director.name} /</td><td>
<br />{$client.signer_position}_____________/{$client.signer_name}/
</td></tr></TBODY></TABLE>
</body></html>
