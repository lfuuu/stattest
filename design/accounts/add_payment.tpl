<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><HEAD>
<TITLE>/ MCN | Маркомнет {ldelim}MCN | Маркомнет{rdelim}
</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=koi8-r">
<META content="&#10;/ MCN | Маркомнет&#10;{ldelim}MCN | Маркомнет{rdelim}&#10;" name=title>
<META content="Компания MCN. Быстрый интернет по технологии ADSL. IP-телефония, VoIP. Хостинг. Collocation. Подключение под ключ, выезд специалистов, оплата после подключения." name=description>
<LINK title=default href="{$PATH_TO_ROOT}main.css" type=text/css rel=stylesheet>
<LINK media=print href="{$PATH_TO_ROOT}print.css" type=text/css rel=stylesheet>
<LINK href="/favicon.ico" rel="SHORTCUT ICON">
</HEAD>

<BODY text=#404040 vLink=#000099 aLink=#000000 link=#000099 bgColor=#efefef>

<h1>Добавление платежей</h1>
<p>{$error_message}</p>
<a href="#" onclick="javascript:opener.location.reload(); self.close()">Закрыть окно</a><br><br>

<form action="?client={$client}&todo=add_payment" method="POST">
<table align="left" border="1" bgcolor="#c5d6e3"   cellpadding="5">
	<tr>
		<TD>Сумма платежа</TD>
		<TD>Платежное поручение</TD>
		<TD>Дата платежа(ГГГГ-ММ-ДД)</TD>
		<td>Счет</td>
		<td>Провести по курсу <br>на день оплаты, <br>в случае переплаты</td>
		<td>Тип платежа</td>


		
	</tr>
	<tr>
		<td><INPUT type="text" name="pay_sum" value="0000.00"></td>
		<td><INPUT type="text" name="pay_pp" value="000"></td>
		<td><INPUT type="text" name="pay_date" value="{$now}"></td>
		<TD><SELECT name="bill">
        		{foreach from=$bills item=bill key=key}
                	<option {if $bill.bill_no == $bill_selected} selected="selected" {/if} value="{$bill.bill_no}">{$bill.bill_no}|{$bill.sum}</option>
                	{/foreach}
        	    </SELECT>
        	</TD>
        	<td><INPUT type="checkbox" name="flag" value='1' checked="true"></td>
        	<TD>
        		<SELECT name="type">
        		<option  value="0">Безнал</option>
        		<option value="1">Непроводной нал USD</option>
        		<option value="2">Непроводной нал РУБ</option>
        		<option value="3">Проводной нал</option>
         		</SELECT>
        	</TD>
	</tr>
		<td> Комментарий к платежу </td>
		<td colspan="5"><textarea name="comment" cols="100" rows="3"></textarea></td>
	<tr>
	</tr>
	<tr>
		<TD colspan="6"><INPUT type="submit" name="payment" value="Внести платеж"></TD>
		
	</tr>

</table>
<INPUT type="hidden" name="saldo" value="{$saldo}">
</form>
</BODY>
</HTML>
