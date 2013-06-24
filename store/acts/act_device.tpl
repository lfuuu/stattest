<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<TITLE></TITLE>
<META http-equiv=Content-Type content="text/html; charset=koi8-r">
<LINK href="{$PATH_TO_ROOT}print.css" type=text/css rel=stylesheet>
</HEAD>
<BODY text="#404040" vLink="#000099" aLink="#000000" link="#000099" bgColor="#EFEFEF">
<h2 align=center>АКТ ПРИЕМА ПЕРЕДАЧИ </h2>
<h3 align=center>по&nbsp;договору &#8470; {$contract.contract_no} от {$contract.contract_date|mdate:"d.m.Y г."}</h3>
<table align=center width=90%><tr><td align=left>г. Москва </td><td align=right>{if $cpe.actual_from<=date('Y-m-d')}<b>{$cpe.actual_from|mdate:"d.m.Y г."}</b>{else}&nbsp;{/if}</td></tr></table>
<br>
<br>
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     Мы, нижеподписавшиеся, {$firm_director.position} {$firm_director.name} {$firma.name}
	 и представитель <b>{$client.company_full}</b>, в&nbsp;лице <b>{$client.signer_positionV} {$client.signer_nameV}</b>, произвели акт приема-передачи во временное пользование следующего оборудования: 
</p>
		<ul> <li>{$cpe.type|upper}-модем {$cpe.vendor} {$cpe.model}, серийный номер {$cpe.serial|upper}, 1 шт.</li></ul><br>
	 Получен залог в сумме {$cpe.deposit_rur*1.18|round:2} рублей</p>
	 <p>&nbsp;</p>
	 <p>&nbsp;</p>
	 <p>&nbsp;</p>
	 <p>&nbsp;</p>

<TABLE cellSpacing=0 cellPadding=0 border=0 width="100%"><TBODY><TR><TD>
	Оператор: {$firma.name}
</td><td>
	Абонент: <b>{$client.company_full}</b>
</td></tr><tr><td>
	<br><br><p>
{$firm_director.position}  ___________ /{$firm_director.name}/ 
</td><td>
	<br><br><p>{$client.signer_position}_____________/{$client.signer_name}/</p>
</td></TR></TBODY></TABLE>
</body></html>
