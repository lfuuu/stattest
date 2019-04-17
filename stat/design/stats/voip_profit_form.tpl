<script src="js/jquery.js"></script>
<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="js/ui/jquery.ui.timepicker.addon.js"></script>
<script src="js/ui/i18n/jquery.ui.datepicker-ru.js"></script>
<script src="js/ui/i18n/jquery.ui.timepicker-ru.js"></script>
<link href="css/themes/base/jquery.ui.all.css" rel="stylesheet" type="text/css"/>

<form action="?" method="get">
      <table class="mform" cellSpacing=4 cellPadding=2 width="100%" border=0>
        <tr>
          <td class=left>Биржа звонков:</td>
          <td>
            <input type=hidden name="module" value="stats">
            <input type=hidden name="action" value="{$action}">
            <div style="width:150px;">
            	<select name="marketPlace" class="select2">
                	{foreach from=$marketPlaces key=key item=item}<option value='{$key}'{if $marketPlace==$key} selected{/if}>{$item}</option>{/foreach}
            	</select>
            </div>
          </td>
        </tr>
        <tr>
          <td class=left>Телефон:</td>
          <td>
            <div style="width:350px;">
            	<select name="phone" class="select2">
                	{foreach from=$phones key=key item=item}<option value='{$key}'{if $phone==$key} selected{/if}>{$item}</option>{/foreach}
            	</select>
            </div>
          </td>
        </tr>
        <tr>
          <td class=left>Дата начала отчёта с </td>
          <td>
              <input class="datepicker-input" type=text name="date_from" value="{$date_from}" id="date_from">
              до <input class="datepicker-input" type=text name="date_to" value="{$date_to}" id="date_to">
              <select name="timezone">
                  {foreach from=$timezones item=item}
                      <option value='{$item}'{if $item==$timezone} selected{/if}>{$item}</option>
                  {/foreach}
              </select>
          </td>
	  	</tr>
        <tr>
          <TD class=left>Выводить по:</TD>
          <td>
              <select name="type">
                  {foreach from=$types key=key item=item}
                      <option value="{$key}"{if $type eq $key} selected="selected"{/if}>{$item}</option>
                  {/foreach}
              </select>
          </td>
        </tr>
		<tr>
			<td class="left">Входящие/Исходящие</td>
			<td>
                <select name="direction">
                    {foreach from=$directions key=key item=item}
                        <option value="{$key}"{if $direction eq $key} selected="selected"{/if}>{$item}</option>
                    {/foreach}
                </select>
			</td>
		</tr>
		<tr>
            <td class=left>Только платные звонки:</td>
            <td>
                <input type="checkbox" name="paidOnly" value='1'{if $paidOnly==1} checked{/if}>
            </td>
        </tr>
	  </table>
      <hr>

      <div align=center>
          <INPUT class=button type=submit value="Сформировать отчёт">
      </div>
</form>
<script type="text/javascript">
{literal}
    $("#date_from").datetimepicker({
        dateFormat: 'dd-mm-yy',
        timeFormat: 'hh:mm',
        hourMin: 0,
        hourMax: 23,
    });
    $("#date_to").datetimepicker({
        dateFormat: 'dd-mm-yy',
        timeFormat: 'hh:mm',
        hourMin: 0,
        hourMax: 23,
    });
{/literal}
</script>