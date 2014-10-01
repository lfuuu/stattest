<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=utf-8">
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">
<center>
<h2>АКТ &#8470; 1-{$client.id}</h2>
<h3>сдачи-приемки работ по&nbsp;подключению к&nbsp;Интернету{if false}<br>
по&nbsp;договору &#8470; {$contract.contract_no} от {$contract.contract_date|mdate:"d.m.Y г."}{/if}
<table align=center width=90%><tr><td align=left>г. Москва </td><td align=right>"__"______________ {*if $conn.actual_from<=date('Y-m-d')}{$conn.actual_from|mdate:"Y"}{else*}{php}echo date("Y");{/php}{*/if*}г.</td></tr></table>
</h3>
</center>
<p>Настоящий акт составлен между Абонентом <b>{$client.company_full}</b>, в&nbsp;лице 
<b>{$client.signer_positionV} {$client.signer_nameV}</b>, и&nbsp;Оператором 
{if $client.firma eq "mcn"}
	ООО &laquo;Эм Си Эн&raquo;, в&nbsp;лице Генерального директора Мельникова&nbsp; А.&nbsp;К., о&nbsp;
{else}
	ООО &laquo;МАРКОМНЕТ&raquo; в&nbsp;лице директора Мельникова&nbsp; А.&nbsp;К., о&nbsp;
{/if} 
том, что:</p>

<ol>
<li>Абоненту был установлено подключение к&nbsp;Интернету по&nbsp;адресу: <b>{$conn.address}</b></li>
<li>Абоненту выданы следующие IP-адреса/IP-сеть: <b>{foreach from=$routes item=item name=outer}{if $smarty.foreach.outer.iteration!=1}, {/if}{$item.net}{/foreach}</b></li>

{if $port.port_type=='adsl'||$port.port_type=='adsl_cards' || $port.port_type=='adsl_karta' || $port.port_type=='adsl_terminal'}
	<li>На телефонной линии <b>{$port.node}</b> абоненту установлено и введено в эксплуатацию следующее оборудование:</li><ul>
	{foreach from=$cpe item=item}{if $item.actual}
	<li>ADSL-модем {$item.vendor} {$item.model}, серийный номер {$item.serial}, 1 шт.</li>
	{/if}{/foreach}
	<li>Сплиттер 1 шт</li>
	<li>__________________________________________________________</li>
	<li>__________________________________________________________</li>
	</ul>
<p>Все вышеперечисленное оборудование передается Абоненту во&nbsp;временное пользование на&nbsp;срок действия Договора.</p>
<p>Перечисленное выше оборудование, каналы связи проверены представителем Абонента, функционируют нормально и&nbsp;удовлетворяют требованиям Договора.</p>
{else}{if $port.port_type=='dedicated' || $port.port_type=='pppoe'}
	<li>Абоненту установлено и&nbsp;введено в&nbsp;эксплуатацию подключение на порту: {$port.port_name}</li>
<p>Каналы связи проверены представителем Абонента, функционируют нормально и удовлетворяют требованиям Договора.</p>
{else}
	<li>Абоненту установлено и&nbsp;введено в&nbsp;эксплуатацию подключение на оборудование: </li><ul>
	{foreach from=$cpe item=item}{if $item.actual}
	<li>{$item.vendor} {$item.model} {$item.serial}</li>
	{/if}{/foreach}
	<li>__________________________________________________________</li>
	<li>__________________________________________________________</li>
	</ul>
<p>Все вышеперечисленное оборудование передается Абоненту во&nbsp;временное пользование на&nbsp;срок действия Договора.</p>
<p>Перечисленное выше оборудование, каналы связи проверены представителем Абонента, функционируют нормально и&nbsp;удовлетворяют требованиям Договора.</p>
{/if}{/if}
</ol>

<TABLE cellSpacing=10 cellPadding=0 border=0 width="100%"><TBODY>
<TR>
<TD width=50% valign=top>{if $port.port_type != "cdma" && $port.port_type != "wimax"}
<h5>Информация для настройки соединения</h5>
{if count($ppp)>0}
<h6>Настройка pppoe</h6>
{foreach from=$ppp item=item}
<p> Логин: <b>{$item.login}</b><br>
Пароль: <b>{$item.password}</b><br>
</p>
{/foreach}
{/if}
<p>Шлюз: <b>{foreach from=$routes item=item name=outer}{if $smarty.foreach.outer.iteration!=1}, {/if}{$item.gate}{/foreach}</b><br>
Первичный DNS: <b>85.94.32.2</b><br>
Вторичный DNS: <b>85.94.63.222</b></p>{/if}
</td><td>
<h5>Информация для получения статистики</h5>
Страница просмотра: <i>https://stat.mcn.ru/client</i><br>

Логин: <b>{$client.client}</b><br>
Пароль: <b>{$client.password}</b>
</p>
</td></TR></TBODY></TABLE>
<hr>
<TABLE cellSpacing=0 cellPadding=0 border=0 width="100%"><TBODY>
<TR>
<TD>
<p>Оператор: {if $client.firma eq "mcn"}
ООО &laquo;Эм Си Эн&raquo; &nbsp;{else}ООО &laquo;МАРКОМНЕТ&raquo;
{/if} </td><td>Абонент: <b>{$client.company_full}</b></td>

</tr><tr><td>
<br><br><p>{if $client.firma eq "mcn"}
Ген. директор {else}Директор{/if} ___________ Мельников&nbsp; А.&nbsp;К. </td><td>
<br><br><p>{$client.signer_position}/_____________/{$client.signer_name}</p>
</td></TR></TBODY></TABLE>
</body></html>
