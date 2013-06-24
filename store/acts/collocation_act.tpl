<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=koi8-r">
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">
<center>
<h2>АКТ</h2>
<h3>сдачи-приемки работ по&nbsp;Collocation-подключению<br>
по&nbsp;договору &#8470; {$contract.contract_no} от {$contract.contract_date|mdate:"d.m.Y г."}
<table align=center width=90%><tr><td align=left>г. Москва </td><td align=right>{if $conn.actual_from<=date('Y-m-d')}<b>{$conn.actual_from|mdate:"d.m.Y г."}</b>{else}&nbsp;{/if}</td></tr></table>
</h3>
</center>
<p>Настоящий акт составлен между Абонентом <b>{$client.company_full}</b>, в&nbsp;лице 
<b>______________________________________________________________________________</b> и&nbsp;Оператором ООО &laquo;МАРКОМНЕТ&raquo; в&nbsp;лице директора Мельникова&nbsp; А.&nbsp;К. о&nbsp;том, что:</p>

<ol>
<li>Абоненту был установлено подключение к&nbsp;Интернету по&nbsp;адресу: <b>{$client.address}</b></li>
<li>Абоненту выданы следующие IP-адреса/IP-сеть: <b>{$route.net}</b></li>

</ul>
<li>Абоненту установлено и&nbsp;введено в&nbsp;эксплуатацию оборудование уплотнения абонентской линии &#8470;&nbsp;<b>{$route.node}</b></li>
<ul><b><i>Список оборудования:</i></b>
<li>ADSL модем: {$route.adsl_modem_serial}</li>
<li>Сплитор 1 шт</li>
<li>__________________________________________________________</li>
<li>__________________________________________________________</li>
</ul>
</ol>
<p>Все вышеперечисленное оборудование передается Заказчику во&nbsp;временное пользование на&nbsp;срок действия Договора.</p>
<p>Перечисленное выше оборудование, каналы связи проверены представителем Заказчика, функционируют нормально и&nbsp;удовлетворяют требованиям Договора.</p>

<TABLE cellSpacing=10 cellPadding=0 border=0 width="100%"><TBODY>
<TR>
<TD width=50% valign=top>
<h5>Информация для настройки соединения</h5>
{if count($ppp)>0}
<h6>Настройка pppoe</h6>
{foreach from=$ppp item=item}
<p> Логин: <b>{$item.login}</b><br>
<p> Пароль: <b>{$item.pass}</b><br>
{/foreach}
{/if}
<p>Шлюз: <b>{$gate}</b><br>
Первичный DNS: <b>{$dns1}</b><br>
Вторичный DNS: <b>{$dns2}</b></p>
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
<p>Оператор: ООО &laquo;МАРКОМНЕТ&raquo; </td><td>Абонент: <b>{$client.company_full}</b></td>

</tr><tr><td>
<p>Директор ___________ Мельников&nbsp; А.&nbsp;К. </td><td>
_____________/_____________/______________</p>
</td></TR></TBODY></TABLE>
</body></html>
