<script>
{literal}

var selectedObj = null;
var selectedBillNo = "";
var saveEntry = "";
var saveCourierId = "";
function doDBL(obj, billNo)
{
    if (selectedObj)
    {
        doCancel();
    }
    selectedObj = obj;
    selectedBillNo = billNo;

    saveEntry = obj.innerHTML;
    obj.innerHTML = '<select id="edit_nal">'+
        '<option value="beznal"'+(saveEntry=='beznal'? ' selected' : '')+'>beznal</option>'+
        '<option value="nal"'+(saveEntry=='nal'? ' selected' : '')+'>nal</option>'+
        '<option value="prov"'+(saveEntry=='prov'? ' selected' : '')+'>prov</option>'+
        '</select><input type=button onclick="doCancel()" value="x"><input type=button onclick="doSave(1)" value="OK!">';

}

function doDBL_courer(obj, billNo, courierId)
{
    if (selectedObj)
    {
        doCancel();
    }
    selectedObj = obj;
    selectedBillNo = billNo;
    saveEntry = obj.innerHTML;
    saveCourierId = courierId;

    var s = '<select id="edit_courier">';
    var oCur = _get("courier").options;
    for(var i = 1 ; i < oCur.length; i++)
    {
        s += '<option value="'+oCur[i].value+'"'+(oCur[i].value == courierId ? ' selected' : '')+'>'+oCur[i].text+'</option>';
    }
    obj.innerHTML = s+
        '</select><input type=button onclick="doCancel()" value="x"><input type=button onclick="doSave(2)" value="OK!">';
}


function doCancel()
{
    if (selectedObj)
    {
        selectedObj.innerHTML = saveEntry;
        selectedObj = null;
        selectedBillNo = "";
        saveEntry = "";
    }
}

function doSave(mod)
{
    _get("bill_no").value = selectedBillNo;

    // save nal
    if (mod == 1)
    {
        _get("obj").value = "nal";
        _get("value").value = _get("edit_nal").options[_get("edit_nal").selectedIndex].value;
    }else{
        // save courier
        _get("obj").value = "courier";
        _get("value").value = _get("edit_courier").options[_get("edit_courier").selectedIndex].value;
    }

    _get("save").value = 1;
    _get("aForm").submit();

}


function _get(n)
{
    return document.getElementById(n);
}

function openAddComment(obj, billNo)
{
    var tr = _get("comment_tr_"+billNo);

    if(tr)
    {
        if(tr.style.display != "")
        {
            tr.style.display = "";
            _get("comment_"+billNo).focus();
            _get("a_"+billNo).innerText = "-";
        }else{
            doCommentCancel(billNo);
        }
    }
}

function doCommentCancel(billNo)
{
    var tr = _get("comment_tr_"+billNo);
    if(tr)
    {
        tr.style.display = "none";
        var a = _get("a_"+billNo);
        if(a)
        {
            a.innerText = "+";
        }
    }
}

function doSaveComment(billNo)
{

    _get("bill_no").value = billNo;
    _get("obj").value = "comment";
    _get("value").value = _get("comment_"+billNo).value;
    _get("save").value = 1;
    _get("aForm").submit();
}

{/literal}
</script>
<h2>Отчет по долгам</h2>
<form action='?' method=get id="aForm">
<input type=hidden name=save id=save value=0>
<input type=hidden name=bill_no id=bill_no value="">
<input type=hidden name=obj id=obj value="">
<input type=hidden name=value id=value value="">

<input type=hidden name=module value=newaccounts>
<input type=hidden name=action value=debt_report>
<input type=hidden name=go value=yes>
Менеджер: <SELECT name=manager>{foreach from=$users_manager item=item key=user}<option value='{$item.user}' {if isset($item.selected)}{$item.selected}{/if}>{$item.name} ({$item.user})</option>{/foreach}</select><br>
Курьер: <select name="courier" id="courier">{html_options options=$l_couriers selected=$courier}</select><br>
Метро: <select name="metro" id="metro">{html_options options=$l_metro selected=$metro}</select><br>
От <input type=text class=text id='date_from' name='date_from' value='{$date_from}'> до <input type=text class=text id='date_to' name='date_to' value='{$date_to}'><br>
<input type=checkbox{if $nal.beznal} checked{/if} name=nal[] value=beznal>Банковский перевод<br>
<input type=checkbox{if $nal.nal} checked{/if} name=nal[] value=nal>Наличными<br>
<input type=checkbox{if $nal.prov} checked{/if} name=nal[] value=prov>Наличными (проведенные)<br><br>
<input type=checkbox checked name='zerobills' value='1'>Не включать нулевые счета<br>
<input type=checkbox value=1 name=cl_off{if $cl_off} checked{/if}>Показывать все счета<br>
<input type=submit class=button value='Просмотр'>
</form>
{if isset($bills)}
{if $get_url}<table border=0 width="100%"><tr><td align=right><a href="{$get_url}&print=1" target=_blank style="font: normal 6pt Arial;">Версия для печати</a></td></tr></table>{/if}
<TABLE class=price cellSpacing=4 cellPadding=2 width="100%" border=0>
<tr><th>&nbsp;</th><th>клиент</th><th>компания</th><th>Менеджер</th><th>дата/н счёта</th><th>сумма</th><th>сальдо</th><th>Тип оплаты</th><th>Курьер</th></tr>
{foreach from=$bills item=item key=key name=outer}<tr class={cycle values="even,odd"}>
<td>{$smarty.foreach.outer.iteration}{if !$item.date} <span style="cursor:pointer; color: rgb(67,101,125);text-decoration: underline;" onclick="openAddComment(this, '{$item.bill_no}');" id="a_{$item.bill_no}">(+)</a>{/if}</td>
<td{if $item.nal!='beznal'} bgcolor='#{if $item.nal == "nal"}FFC0C0{else}C0C0FF{/if}'{/if}><a href='{$LINK_START}module=newaccounts&action=bill_list&clients_client={$item.client}'>{$item.client}</a></td>
<td style='font-size:85%'>{$item.company}</td>
<td>{$item.manager}</td>
<td>{$item.bill_date} - <a href='{$LINK_START}module=newaccounts&action=bill_view&bill={$item.bill_no}'>{$item.bill_no}</a></td>
<td align=right>{$item.sum|round:2} {if $item.currency=='USD'}$ {if $item.gen_bill_rub!=0}<br><span style='font-size:85%' title='Сумма счёта, {$item.gen_bill_date}'>b {$item.gen_bill_rub} р</span>{/if}{else}р.{/if} </td>
<td align=center>{$item.debt.sum|round:2}{if $item.debt.currency=='USD'}${else}р.{/if} </td>
<td onselect="return false" ondblclick="doDBL(this, '{$item.bill_no}')" align=center{if $item.bill_nal!='beznal'} bgcolor='#{if $item.bill_nal == "nal"}FFC0C0{else}C0C0FF{/if}'{/if}>{$item.bill_nal}</td>
<td ondblclick="doDBL_courer(this, '{$item.bill_no}','{$item.courier_id}')">{$item.courier|replace:"-":""}</td>
</tr>
<tr{if !$item.date} style="display: none;"{/if} id="comment_tr_{$item.bill_no}"><td colspan=3 align=right>{$item.date}</td><td>{$item.user}</td><td colspan=4><textarea id="comment_{$item.bill_no}" style="width:400px;">{$item.comment}</textarea></td><td><input type="button" onclick="doSaveComment('{$item.bill_no}')" value="OK">{if !$item.date}<input type="button" value="x" onclick="doCommentCancel('{$item.bill_no}')">{/if}</td></tr>
{/foreach}

<tr style='background:#FFFFFF'>
<td colspan=5 align=right><b>Всего по долларовым счетам:</b></td>
<td align=right>{$bills_total_USD.sum|round:2} $</td>
<td align=center>{$bills_total_USD.saldo|round:2} $ </td>
</tr>
<tr style='background:#FFFFFF'>
<td colspan=5 align=right><b>Всего по рублёвым счетам:</b></td>
<td align=right>{$bills_total_RUB.sum|round:2} р</td>
<td align=center>{$bills_total_RUB.saldo|round:2} р</td>
</tr>
</TABLE>
{/if}
<script>
optools.DatePickerInit();
</script>