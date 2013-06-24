<h2>Автоматическое выставление счетов</h2>
<table border="1">
<tr>
<TD>
<a href="modules/accounts/print_bills.php?i=0&t=all">
Все счета
</a>
</TD>
<TD>
<a href="modules/accounts/print_bills.php?i=0&t=pma">
Pma счета
</a>
</TD>
<TD>
<a href="modules/accounts/print_bills.php?i=0&t=bnv">
Bnv счета
</a>
</TD>
<TD>
<a href="modules/accounts/print_bills.php?i=0&t=voip">
VoIP счета
</a>
</TD>
</tr>
</table>

<h2>Печать счетов с помощью фреймов</h2>
<table border="1" cellpadding="10">
<TD>
{foreach from=$managers item=m key=key}
<TD  valign="top" align="left">
	<b>{$key}</b><br>
	{foreach from=$m item=item key=k}
	<a href="modules/accounts/print_bills_fr.php?i=0&t={$key}&limit_from={$item}">
		Счета_Партия_{$k}
	</a><br>
	{/foreach}
</TD>
{/foreach}
</tr>
</table>
