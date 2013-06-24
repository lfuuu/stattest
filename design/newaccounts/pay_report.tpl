<H2>Отчёт по платежам</H2>
<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="js/ui/i18n/jquery.ui.datepicker-ru.js"></script>
<link href="css/themes/base/jquery.ui.all.css" rel="stylesheet" type="text/css"/>

      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom>Клиент</TD>
          <TD class=header vAlign=bottom>Компания</TD>
          <TD class=header vAlign=bottom>мнджр</TD>
          <TD class=header vAlign=bottom colspan=3>Платеж</TD>
          <TD class=header vAlign=bottom>пров.</TD>
          <TD class=header vAlign=bottom>Сумма-курс</TD>
          <TD class=header vAlign=bottom>Счёт</TD>
          <TD class=header vAlign=bottom>Кто</TD>
          <TD class=header vAlign=bottom>Когда</TD>
          <TD class=header vAlign=bottom>Комментарий</TD>
        </TR>

{foreach from=$payments item=item}<tr>
	<td>{if !$fullscreen}<a href='{$LINK_START}module=clients&id={$item.client_id}'>{$item.client}</a>{else}{$item.client}{/if}</td>
	<td style='font-size:85%'>{$item.company}</td>
	<td>{$item.manager}</td>
	<td>{$item.bank}</td>
	<td>{$item.payment_no}</td>
	<td>{$item.payment_date}</td>
	<td>{$item.oper_date}</td>
	<td>{$item.sum_rub}{if $item.currency=='USD'}${else}р{/if}{if $item.payment_rate!=1}<span style='font-size:85%'> / {$item.payment_rate}</span>{/if}</td>
	<td>{$item.bill_no}<span style='font-size:85%'> от {$item.bill_date}</span></td>
	<td style='font-size:85%'>{$item.user}</td>
	<td style='font-size:85%'>{$item.add_date}</td>
	<td style='font-size:85%'>{$item.comment}</td>
</tr>{/foreach}

<tr><td colspan=2>Сумма по RUR</td><td>b</td><td></td><td></td><td>{$totals.bRUR}р</td><td></td><td></td><td></td><td></td></tr>
<tr><td colspan=2>Сумма по RUR</td><td>p</td><td></td><td></td><td>{$totals.pRUR}р</td><td></td><td></td><td></td><td></td></tr>
<tr><td colspan=2>Сумма по RUR</td><td>n</td><td></td><td></td><td>{$totals.nRUR}р</td><td></td><td></td><td></td><td></td></tr>
<tr><td colspan=2>Сумма по RUR</td><td></td><td></td><td></td><td>{$totals.RUR}р</td><td></td><td></td><td></td><td></td></tr>
{if $totals.bUSD+$totals.pUSD+$totals.nUSD!=0}
	<tr><td colspan=2>Сумма по USD</td><td>b</td><td></td><td></td><td>{$totals.bUSD}$</td><td></td><td></td><td></td><td></td></tr>
	<tr><td colspan=2>Сумма по USD</td><td>p</td><td></td><td></td><td>{$totals.pUSD}$</td><td></td><td></td><td></td><td></td></tr>
	<tr><td colspan=2>Сумма по USD</td><td>n</td><td></td><td></td><td>{$totals.nUSD}$</td><td></td><td></td><td></td><td></td></tr>
	<tr><td colspan=2>Сумма по USD</td><td></td><td></td><td></td><td>{$totals.USD}$</td><td></td><td></td><td></td><td></td></tr>
{/if}
</tbody></table>

{if !$fullscreen}

		<FORM action="?" method=get>
		<input type=hidden name=module value=newaccounts>
		<input type=hidden name=action value=pay_report>

        <script>
{literal}

function checkPeriod()
{
    var isCheckedDay = $("#range_by_day").is(":checked");

    if(isCheckedDay)
    {
        $("#by_day").css("display", "");
        $("#by_period").css("display", "none");
    }else{
        $("#by_day").css("display", "none");
        $("#by_period").css("display", "");
    }

}

{/literal}
        </script>

      <TABLE class=mform cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=left>Период:</TD>
          <TD>
            <input onclick="checkPeriod()" type=radio name=range_by value=day id=range_by_day{if $by_day} checked{/if}><label for=range_by_day>день</label>
            <input onclick="checkPeriod()" type=radio name=range_by value=period id=range_by_period{if !$by_day} checked{/if}><label for=range_by_period>период</label>
            </td>
        </tr>
        <TR id=by_day{if !$by_day} style="display:none;"{/if}>
          <TD class=left>Дата отчёта</TD>
          <TD>
              <input type=text id=from_day name=from_day value="{$from_day}">
        </TD>
        </TR>
        <TR id=by_period{if $by_day} style="display:none;"{/if}>
          <TD class=left>Дата отчёта</TD>
          <TD>
              с <input type=text name=from_period id=from_period value="{$from_period}">
              по <input type=text name=to_period id=to_period value="{$to_period}">
        </TD>
        </TR>
        <TR>
          <TD class=left>Какую дату смотреть</TD>
          <TD>
          		<select name=type>
          		<option value=payment_date{if $type=='payment_date'} selected{/if}>дата платежа</option>
          		<option value=add_date{if $type=='add_date'} selected{/if}>дата занесения</option>
          		<option value=oper_date{if $type=='oper_date'} selected{/if}>дата проведения</option>
          		</select>
			</td></tr>
        <tr>
          <TD class=left>Типы платежей</TD>
          <td>
          		<input type=checkbox name=bank{if $bank} checked{/if}>b
          		<input type=checkbox name=prov{if $prov} checked{/if}>p
          		<input type=checkbox name=neprov{if $neprov} checked{/if}>n
			</td></tr>
        <tr>
          <TD class=left>Банки</TD>
          <td valign=top>
          		<input type=checkbox name=banks[mos]{if $banks.mos} checked{/if}>Банк Москвы
          		<input type=checkbox name=banks[citi]{if $banks.citi} checked{/if}>СитиБанк
          		<input type=checkbox name=banks[ural]{if $banks.ural} checked{/if}>УралСиб
          		<input type=checkbox name=banks[sber]{if $banks.sber} checked{/if}>СберБанк
			</td></tr>
		<tr>
			<td class="left">Пользователь: </td>
			<td>
				<select name="user">
					<option value="0">Все</option>
					{foreach from=$users item='u'}
					<option value="{$u.id}"{if $u.id==$user} selected='selected'{/if}>{$u.name} - {$u.user}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td class="left">На весь экран: </td>
			<td>
                <input type=checkbox name=fullscreen>
			</td>
		</tr>
        </TBODY></TABLE>
      <HR />

        <script>
{literal}
$("#from_day").datepicker({dateFormat: 'dd-mm-yy'});

$( "#from_period" ).datepicker({
    dateFormat: 'dd-mm-yy',
    changeMonth: true,
    onClose: function( selectedDate ) {
    $( "#to_period" ).datepicker( "option", "minDate", selectedDate );
}
});

$( "#to_period" ).datepicker({
    dateFormat: 'dd-mm-yy',
    changeMonth: true,
    onClose: function( selectedDate ) {
    $( "#from_period" ).datepicker( "option", "maxDate", selectedDate );
}
});

{/literal}
        </script>
      <DIV align=center><INPUT class=button type=submit value="Сформировать отчёт"></DIV></FORM>
{/if}
