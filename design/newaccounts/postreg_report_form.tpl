<H2>Почтовый реестр</H2>

      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
            <FORM action="?" method=get>
            <input type=hidden name=module value=newaccounts>
            <input type=hidden name=action value=postreg_report_do>
        <TR>
          <TD class=left>Дата реестра</TD>
          <TD>
          <input type="text" value="{$date_from}" id="date_from" name="date_from" class="text">
          </TD></TR>
        </TBODY></TABLE>
      <HR>

      <DIV align=center><INPUT class=button type=submit value="Сформировать"></DIV></FORM>
      <script>
      optools.DatePickerInit();
      </script>
