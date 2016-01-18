<?php

use app\classes\Html;
use app\models\Region;
?>

<h2>Массовые операции со счетами</h2>
<form action="?" method="GET">
    <input type="hidden" name="module" value="newaccounts" />
    <input type="hidden" name="action" value="bill_mass" />
    <input type="hidden" name="obj" value="print" />
    Напечатать
    <input type="checkbox" name="do_bill" checked="checked" />счета
    <input type="checkbox" name="do_inv" />счет-фактуры
    <input type="checkbox" name="do_akt" />акты
    <select name="date">
        <option value="month">созданные в текущем месяце</option>
        <option value="today">созданные сегодня</option>
        <option value="paytoday">с сегодняшними платежами</option>
    </select>
    <input type="submit" value="Печать" class="button" />
</form>
<br /><br />

<a href="?module=newaccounts&action=bill_mass&obj=create" target="_blank" onClick="return confirm('Точно?')">Выставить счета всем клиентам за текущий месяц</a><br />
<br />
<a href="./?module=newaccounts&action=bill_mass&obj=print">Печать всех счетов за текущий месяц</a><br />
<br />
<a href="./?module=newaccounts&action=bill_balance_mass" target="_blank" onClick="return confirm('Точно?')">Обновить баланс всем клиентам</a><br />
<br />
<a href="./?module=newaccounts&action=bill_publish">Опубликовать счета выставленные в этом месяце</a><br />
<br />
<div class="well" style="width: 400px;">
    <fieldset>
        <label>Публикация счетов в регионе</label>
        <form action="/bill/publish/region">
            <div class="col-xs-12">
                <div class="col-xs-6">
                    <?php
                    echo Html::dropDownList(
                        'region',
                        Region::HUNGARY,
                        ['' => 'Укажите регион'] + Region::getList(),
                        ['class' => 'form-control select2', 'style' => 'width: 160px;',]
                    );
                    ?>
                </div>
                <div class="col-xs-6">
                    <input type="submit" value="Опубликовать" class="btn btn-primary" />
                </div>
            </div>
        </form>
    </fieldset>
</div>