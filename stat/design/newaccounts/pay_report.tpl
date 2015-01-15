<H2 id="top">Отчёт по платежам</H2>
<script src="js/jquery-ui-1.9.2.custom.min.js"></script>
<script src="js/ui/i18n/jquery.ui.datepicker-ru.js"></script>
<link href="css/themes/base/jquery.ui.all.css" rel="stylesheet" type="text/css"/>

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
          		<input type=checkbox name=ecash{if $ecash} checked{/if}>Эл. деньги
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
          <TD class=left>Операторы электронных денег</TD>
          <td valign=top>
          		<input type=checkbox name=ecashs[cyberplat]{if $ecashs.cyberplat} checked{/if}>Cyberplat
          		<input type=checkbox name=ecashs[yandex]{if $ecashs.yandex} checked{/if}>Яндекс.Деньги
          		<input type=checkbox name=ecashs[uniteller]{if $ecashs.uniteller} checked{/if}>Uniteller
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
			<td class="left">Сортировать по: </td>
			<td>
				<select name="order_by">
					<option value="add_date" {if $order_by == "add_date"}selected{/if}>Дате заненсения платежа</option>
					<option value="C.client" {if $order_by == "C.client"}selected{/if}>Клиенту</option>
					<option value="payment_no" {if $order_by == "payment_no"}selected{/if}>Номеру платежа</option>
					<option value="sum_rub" {if $order_by == "sum_rub"}selected{/if}>Сумме</option>
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

      <TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
        <TBODY>
        <TR>
          <TD class=header vAlign=bottom rowspan=2>Клиент</TD>
          <TD class=header vAlign=bottom rowspan=2>Компания</TD>
          <TD class=header vAlign=bottom rowspan=2>мнджр</TD>
          <TD class=header vAlign=bottom colspan=3>Информация о платеже</TD>
          <TD class=header vAlign=bottom rowspan=2>Дата проводки</TD>
          <TD class=header vAlign=bottom rowspan=2>Сумма</TD>
          <TD class=header vAlign=bottom rowspan=2>Счёт</TD>
          <TD class=header vAlign=bottom colspan=2>Занесение платежа</TD>
          <TD class=header vAlign=bottom rowspan=2>Комментарий</TD>
        </TR>
        <TR>
          <TD class=header vAlign=bottom>Банк</TD>
          <TD class=header vAlign=bottom>Номер</TD>
          <TD class=header vAlign=bottom>Дата</TD>
          <TD class=header vAlign=bottom>Кто</TD>
          <TD class=header vAlign=bottom>Когда</TD>
        </tr>

{foreach from=$payments item=item}<tr>
	<td>{if !$fullscreen}<a href='{$LINK_START}module=clients&id={$item.client_id}'>{$item.client}</a>{else}{$item.client}{/if}</td>
	<td style='font-size:85%'>{$item.company}</td>
	<td>{$item.manager}</td>
	<td>{if $item.type=='e'}{$item.ecash_operator}{else}{$item.bank}{/if}</td>
	<td>{$item.payment_no}</td>
	<td>{$item.payment_date}</td>
	<td>{$item.oper_date}</td>
	<td align=right>{$item.sum_rub|num_format:true:2}{if $item.currency=='USD'}${else}р{/if}{if $item.payment_rate!=1}<span style='font-size:85%'> / {$item.payment_rate}</span>{/if}</td>
	<td>{$item.bill_no}<span style='font-size:85%'> от {$item.bill_date}</span></td>
	<td style='font-size:85%'>{$item.user}</td>
	<td style='font-size:85%'>{$item.add_date}</td>
	<td style='font-size:85%'>{$item.comment}</td>
</tr>{/foreach}

<tr><td colspan=2>Сумма по RUB</td><td>b</td><td></td><td></td><td align=right>{$totals.bRUB|num_format:true:2}р</td><td></td><td></td><td></td><td></td></tr>
<tr><td colspan=2>Сумма по RUB</td><td>p</td><td></td><td></td><td align=right>{$totals.pRUB|num_format:true:2}р</td><td></td><td></td><td></td><td></td></tr>
<tr><td colspan=2>Сумма по RUB</td><td>n</td><td></td><td></td><td align=right>{$totals.nRUB|num_format:true:2}р</td><td></td><td></td><td></td><td></td></tr>
<tr><td colspan=2>Сумма по RUB</td><td>Эл. деньги</td><td></td><td></td><td align=right>{$totals.eRUB|num_format:true:2}р</td><td></td><td></td><td></td><td></td></tr>
<tr><td colspan=2>Сумма по RUB</td><td></td><td></td><td></td><td align=right>{$totals.RUB|num_format:true:2}р</td><td></td><td></td><td></td><td></td></tr>
{if $totals.bUSD+$totals.pUSD+$totals.nUSD!=0}
	<tr><td colspan=2>Сумма по USD</td><td>b</td><td></td><td></td><td align=right>{$totals.bUSD|num_format:true:2}$</td><td></td><td></td><td></td><td></td></tr>
	<tr><td colspan=2>Сумма по USD</td><td>p</td><td></td><td></td><td align=right>{$totals.pUSD|num_format:true:2}$</td><td></td><td></td><td></td><td></td></tr>
	<tr><td colspan=2>Сумма по USD</td><td>n</td><td></td><td></td><td align=right>{$totals.nUSD|num_format:true:2}$</td><td></td><td></td><td></td><td></td></tr>
	<tr><td colspan=2>Сумма по USD</td><td></td><td></td><td></td><td align=right>{$totals.USD|num_format:true:2}$</td><td></td><td></td><td></td><td></td></tr>
{/if}
<tr>
	<td colspan="10" align=right>
		<a onclick="$('body,html').animate({ldelim}scrollTop:100{rdelim},200);">Начало отчета</a>
	</td>
<tr>
</tbody></table>