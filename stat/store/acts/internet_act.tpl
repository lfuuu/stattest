<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=UTF-8">
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">
<center>
<h2>АКТ &#8470; 1-{$client.id}</h2>
<h3>сдачи-приемки работ по&nbsp;подключению к&nbsp;Интернету{if false}<br>
по&nbsp;договору &#8470; {$contract.contract_no} от {$contract.contract_date|mdate:"d.m.Y г."}{/if}
<table align=center width=90%><tr><td align=left><b>г. Москва</b></td><td align=right><b>{if $conn.actual_from<=date('Y-m-d')}{$conn.actual_from|mdate:"d.m.Y г."}{else}{*php}echo date("Y");{/php*}{/if}</b></td></tr></table>
</h3>
</center>
<p>Настоящий акт составлен между Абонентом <b>{$client.company_full}</b>, в&nbsp;лице
<b>{$client.signer_positionV} {$client.signer_nameV}</b>, и&nbsp;Оператором 
	<b>{$firma.name}</b>, в&nbsp;лице <b>{$firm_director.position_} {$firm_director.name_}</b>, о&nbsp;
том, что:</p>

<ol>
<li>Абоненту был установлено подключение к&nbsp;Интернету по&nbsp;адресу: <b>{$conn.address}</b></li>
{if count($routes) > 0}<li>Абоненту выданы следующие IP-адреса/IP-сеть: <b>{foreach from=$routes item=item name=outer}{if $smarty.foreach.outer.iteration!=1}, {/if}{$item.net}{/foreach}</b></li>{/if}

{if $port.port_type=='adsl'||$port.port_type=='adsl_cards' || $port.port_type=='adsl_karta' || $port.port_type=='adsl_rabota' || $port.port_type=='adsl_terminal' || $port.port_type=='adsl_tranzit1'}
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
{if $port.port_type!='yota'}
<p>Все вышеперечисленное оборудование передается Абоненту во&nbsp;временное пользование на&nbsp;срок действия Договора.</p>
{/if}
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
Страница просмотра: <i>https://lk.mcn.ru/</i><br>

Логин: <b>{$client.client}</b><br>
Пароль: <b>{$client.password}</b>
</p>
</td></TR></TBODY></TABLE>
<hr>
<TABLE cellSpacing=0 cellPadding=0 border=0 width="100%"><TBODY>
<TR>
<TD>
<p>Оператор: <b>{$firma.name}</b></td><td>Абонент: <b>{$client.company_full}</b></td>

</tr><tr><td>
<br><br><p>
{$firm_director.position} ___________ /{$firm_director.name}/</td><td>
<br><br><p>{$client.signer_position}_____________/{$client.signer_name}/</p>
</td></TR></TBODY></TABLE>
</body></html>
