<h1>Печать счетов фактур, актов и конвертов</h1>
<br>
<TABLE  align="center" cellspacing="10">
  <TR bgcolor="#eee0b9">
    <TD>Печать счетов фактур</TD>
    <TD>Печать актов</TD>
    <TD>Печать конвертов</TD>
  </TR>
  <tr>
  	<TD bgcolor="#eee0b9">
  		<form action="modules/accounts/print_invoices.php?todo=print_invoices" method="POST" target="_blank">
  			c<INPUT size="12" value="{$now}" name="start" type="text"> до<INPUT size="12" value="{$now}" name="finish" type="text"><br>
        		<INPUT value="Подготовить Печать" name="submit" type="submit">
      		</form>
  	</TD>
  	<TD bgcolor="#eee0b9">
  		<form action="modules/accounts/print_invoices.php?todo=print_akt" method="POST" target="_blank">
  			c<INPUT size="12" value="{$now}" name="start" type="text"> до<INPUT size="12" value="{$now}" name="finish" type="text"><br>
        		<INPUT value="Подготовить Печать" name="submit" type="submit">
      		</form>
  	</TD>
  	<TD bgcolor="#eee0b9">
  		<form action="modules/accounts/print_invoices.php?todo=print_envelopes" method="POST" target="_blank">
  			c<INPUT size="12" value="{$now}" name="start" type="text"> до<INPUT size="12" value="{$now}" name="finish" type="text"><br>
        		<INPUT value="Подготовить Печать" name="submit" type="submit">
      		</form>
  	</TD>
  </tr>
</TABLE>
