<ul class="breadcrumb">
    <li>
        <a href="/">Главная</a>
    </li>
    <li>
        <a href="/client/view?id={$bill.client_id}">Аккаунт: {$bill.client_id}</a>
    </li>
    <li>
        <a href="/?module=newaccounts&action=bill_view&bill={$bill.bill_no}">Счет №{$bill.bill_no}</a>
    </li>
    <li>
        <a href="?module=newaccounts&action=bill_edit&bill={$bill.bill_no}">Редактирование</a>
    </li>

</ul>

<h2>Бухгалтерия {$fixclient}</h2>
<H3>Редактирование проводки</H3>
<form action="?" method=post id=form name=form>
    <input type=hidden name=module value=newaccounts>
    <input type=hidden name=bill value={$bill.bill_no}>
    <input type=hidden name=action value=bill_apply>
    <input type=hidden name=client_id value={$bill.client_id}>

    <div class="well">
        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Дата проводки:</label>
                    <input class="form-control input-sm" type=text id=bill_date_from name=bill_date
                           value="{$bill_date}">
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label>Валюта проводки: </label>
                    <input class="form-control input-sm" type=text value="{$bill.currency}" readonly>
                </div>
            </div>

            {if $show_bill_no_ext || access('newaccounts_bills', 'edit_ext')}
            <div class="col-sm-2">
                <div class="form-group">
                    <label>Внешний счет: </label>
                    <input class="form-control input-sm" type=text name=bill_no_ext value="{$bill_ext.ext_bill_no}">
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group">
                    <label>Дата внешнего счета: <input type="checkbox" value="Y"
                                                       name="date_from_active" {if $bill_ext.ext_bill_date} checked{/if}
                                                       onchange="activateDatePicker(this, 'date_from');"> </label>
                    <div class="form-group ">
                        <input class="form-control input-sm" {if !$bill_ext.ext_bill_date} disabled="disabled"{/if}
                               id=date_from type=text name=bill_no_ext_date
                               value="{$bill_ext.ext_bill_date}">

                    </div>
                </div>
            </div>
            {else}
                <div class="col-sm-4">&nbsp;</div>
            {/if}
        </div>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Оплатить до:</label>
                    <input class="form-control input-sm" type=text id=pay_bill_until name=pay_bill_until
                           value="{$pay_bill_until}">
                </div>
            </div>

            <div class="col-sm-4">
                <div class="form-group">
                    <label>Предполагаемый тип платежа: </label>
                    <select name="nal" class="form-control">
                        <option value="beznal"{if $bill.nal=="beznal"} selected{/if}>безнал</option>
                        <option value="nal"{if $bill.nal=="nal"} selected{/if}>нал</option>
                        <option value="prov"{if $bill.nal=="prov"} selected{/if}>пров</option>
                    </select>
                </div>
            </div>

            {if $show_bill_no_ext || access('newaccounts_bills', 'edit_ext')}
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Номер внешнего акта</label>
                        <input type="text" class="form-control input-sm" name="akt_no_ext"
                               value="{$bill_ext.ext_akt_no}">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label>Дата внешнего акта: <input type="checkbox" value="Y"
                                                          name="date_akt" {if $bill_ext.ext_akt_date} checked{/if}
                                                          onchange="activateDatePicker(this, 'akt_date_ext');">
                        </label>
                        <input type="text" class="form-control input-sm" name="akt_date_ext" id="akt_date_ext"
                                {if !$bill_ext.ext_akt_date} disabled="disabled"{/if}
                               value="{$bill_ext.ext_akt_date}">
                    </div>
                </div>
            {else}
                <div class="col-sm-4">&nbsp;</div>
            {/if}
        </div>

        <div class="row">
            <div class="col-sm-4">
                <div class="form-group">
                    <label>Исполнитель:</label>
                    {html_options name='courier' options=$l_couriers selected=$bill.courier_id}
                </div>
            </div>

            {if !$bill.uu_bill_id && $clientAccountVersion == 5}
                <div class="col-sm-4">
                    <div class="form-group">

                        <label>Включить в У-с/ф:</label>
                        <input type="checkbox" value="Y"
                               name="is_to_uu_invoice" {if $bill.is_to_uu_invoice} checked{/if}>
                    </div>
                </div>
            {/if}

            <div class="col-sm-4">
                <div class="form-group">
                    <label>
                        Цена включает НДС
                        <input type="checkbox" value="Y"
                               name="price_include_vat" {if $bill.price_include_vat > 0} checked{/if}>
                    </label>
                </div>
            </div>

        {if $show_bill_no_ext || access('newaccounts_bills', 'edit_ext')}
            <div class="col-sm-2">
                <div class="form-group">
                    <label>Номер внешней с/ф</label>
                    <input type="text" class="form-control input-sm" name="invoice_no_ext"
                           value="{$bill_ext.ext_invoice_no}">
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group">
                    <label>Дата внешней с/ф: <input type="checkbox" value="Y"
                                                    name="invoice_date_active" {if $bill_ext.ext_invoice_date} checked{/if}
                                                    onchange="activateDatePicker(this, 'invoice_ext_date');"> </label>
                    <div class="form-group">
                        <input class="form-control input-sm" {if !$bill_ext.ext_invoice_date} disabled="disabled"{/if}
                               id=invoice_ext_date type=text name=invoice_date_ext
                               value="{$bill_ext.ext_invoice_date}">

                    </div>
                </div>
            </div>
        {else}
            <div class="col-sm-4">&nbsp;</div>
        {/if}
        </div>

        <div class="row">
            <div class="col-sm-11"></div>
            <div class="col-sm-1">
                <input id='submit' class='btn btn-primary' type='submit' value="Сохранить">
            </div>
        </div>
    </div>

    {assign var="isDisabledLines" value=false}
    {if $bill.uu_bill_id}
        {assign var="isDisabledLines" value=true}
    {/if}

    <table class="table table-condensed table-striped">
        <tr>
            <th width=1%>&#8470;</th>
            <th width=80%>Наименование</th>
            <th>Количество</th>
            <th>Цена</th>
            <th>Тип</th>
            <th>
                Удаление
                <input type="checkbox" id="mark_del"
                       onchange="if (this.checked) $('input.mark_del').attr('checked','checked'); else $('input.mark_del').removeAttr('checked');"{if $isDisabledLines} disabled{/if}/>
            </th>
        </tr>
        {foreach from=$bill_lines item=item key=key name=outer}
            <tr>
                <td>{$smarty.foreach.outer.iteration}.</td>
                <td><input class="form-control input-sm"
                           value="{if isset($item.item)}{$item.item|escape:"input_value_quotes"}{/if}"
                           name=item[{$key}]{if $isDisabledLines} disabled{/if}></td>
                <td><input class="form-control input-sm" style="width: 100px"
                           value="{if isset($item.amount)}{$item.amount}{/if}"
                           name=amount[{$key}]{if $isDisabledLines} disabled{/if}></td>
                <td><input class="form-control input-sm" style="width: 80px"
                           value="{if isset($item.price)}{$item.price}{/if}"
                           name=price[{$key}]{if $isDisabledLines} disabled{/if}></td>
                <td>
                    <select class="form-control input-sm" style="width: 90px"
                            name=type[{$key}]{if $isDisabledLines} disabled{/if}>
                        <option value='service'{if isset($item.type) && $item.type=='service'} selected{/if}>услуга
                            &nbsp; &nbsp; &nbsp;обычная
                        </option>
                        <option value='zalog'{if isset($item.type) && $item.type=='zalog'} selected{/if}>залог &nbsp;
                            &nbsp;&nbsp; &nbsp;&nbsp;(попадает в с/ф-3)
                        </option>
                        <option value='zadatok'{if isset($item.type) && $item.type=='zadatok'} selected{/if}>задаток
                            &nbsp; (не попадает в с/ф)
                        </option>
                        <option value='good'{if isset($item.type) && $item.type=='good'} selected{/if}>товар</option>
                    </select>
                </td>
                <td><input type="checkbox" class="mark_del" name="del[{$key}]"
                           value="1"{if $isDisabledLines} disabled{/if}/></td>
            </tr>
        {/foreach}
    </table>
    <div style="text-align: center">
        <input id='submit' class='btn btn-primary' type='submit' value="Сохранить"{if $isDisabledLines} disabled{/if}>
    </div>
</form>

<script>
    {literal}

    $('#bill_date_from').datepicker({
      dateFormat: 'dd-mm-yy',
      maxDate: $('#pay_bill_until').val(),
      onClose: function (selectedDate) {
        $('#pay_bill_until').datepicker('option', 'minDate', selectedDate);
      }
    });

    $('#pay_bill_until').datepicker({
      dateFormat: 'dd-mm-yy',
      minDate: $('#bill_date_from').val(),
      onClose: function (selectedDate) {
        $('#bill_date_from').datepicker('option', 'maxDate', selectedDate);
      }
    });

    $('#date_from').datepicker({
      dateFormat: 'dd-mm-yy',
    });

    $('#akt_date_ext').datepicker({
      dateFormat: 'dd-mm-yy',
    });

    $('#invoice_ext_date').datepicker({
      dateFormat: 'dd-mm-yy',
    });


    function activateDatePicker(elm, elemId) {
      $('#' + elemId).attr('disabled', !elm.checked);
    }

    function mark_del() {
      if (document.getElementById('mark_del').checked)
        $('input.mark_del').attr('checked', 'checked');
      else
        $('input.mark_del').removeAttr('checked');
    }

    {/literal}
</script>