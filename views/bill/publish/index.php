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
<a href="./?module=newaccounts&action=bill_balance_mass" target="_blank" onClick="return confirm('Точно?')">Обновить баланс всем клиентам</a><br />
<br />
<a href="./?module=newaccounts&action=bill_publish">Опубликовать счета выставленные в этом месяце</a><br />
<br />

<a href="/bill/bill/mass-invoices">Сформировать с/ф</a><br />
<br />



<div class="well" style="width: 500px;">
    <fieldset>
        <label>Публикация счетов в регионе</label>
        <form action="/bill/publish/region">
            <div class="col-sm-12">
                <div class="col-sm-8">
                    <?php
                    echo Html::dropDownList(
                        'regionId',
                        $regionId,
                        ['' => 'Укажите регион'] + Region::getList(),
                        ['class' => 'form-control select2', 'style' => 'width: 160px;',]
                    );
                    ?>
                </div>
                <div class="col-sm-4">
                    <input type="submit" value="Опубликовать" class="btn btn-primary" />
                </div>
            </div>
        </form>
    </fieldset>
</div>

<div class="well" style="width: 500px;">
    <fieldset>
        <label>Публикация счетов по организации</label>
        <form action="/bill/publish/organization">
            <div class="col-sm-12">
                <div class="col-sm-8">
                    <?php
                    echo Html::dropDownList(
                        'organizationId',
                        $organizationId,
                        ['' => 'Выберите организацию'] + \app\models\Organization::dao()->getList(),
                        ['class' => 'form-control select2', 'style' => 'width: 250px;',]
                    );
                    ?>
                </div>
                <div class="col-sm-4">
                    <input type="submit" value="Опубликовать" class="btn btn-primary" />
                </div>
            </div>
        </form>
    </fieldset>
</div>
<div class="well" style="width: 500px;">
    <fieldset>
        <label>Обновление баланса по организации</label>
        <form action="/?module=newaccounts&action=bill_balance_mass" method="post">
            <div class="col-sm-12">
                <div class="col-sm-8">
                    <?php
                    echo Html::dropDownList(
                        'organizationId',
                        $organizationId,
                        ['' => 'Выберите организацию'] + \app\models\Organization::dao()->getList(),
                        ['class' => 'form-control select2', 'style' => 'width: 250px;',]
                    );
                    ?>
                </div>
                <div class="col-sm-4">
                    <input type="submit" value="Обновить баланс" class="btn btn-primary" />
                </div>
            </div>
        </form>
    </fieldset>
</div>

<div class="well" style="width: 500px;">
    <fieldset>
        <label>Создание с/ф по организации</label>
        <form action="/bill/publish/invoices" method="post">
            <div class="col-sm-12">
                <div class="col-sm-8">
                    <?php
                    echo Html::dropDownList(
                        'organizationId',
                        $organizationId,
                        ['' => 'Выберите организацию'] + \app\models\Organization::dao()->getList(),
                        ['class' => 'form-control select2', 'style' => 'width: 250px;',]
                    );
                    ?>
                </div>
                <div class="col-sm-4">
                    <input type="submit" value="Сгенерировать" class="btn btn-primary" />
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-8">
                        <a href="/bill/publish/invoices-for-all">Сгенерировать для всех организаций</a>
                    </div>
                </div>
            </div>
        </form>
    </fieldset>
</div>


<div class="well text-center" style="width: 500px;">
    <?php if (!$isNotificationsOn) : ?>
        <a class="btn <?=($isNotificationsRunning ? 'btn-warning' : 'btn-primary')?>" href="/monitoring/notification-off" role="button">Отключить оповещения</a>
    <?php else: ?>
        <a class="btn <?=($isNotificationsRunning ? 'btn-danger' : 'btn-info')?>" href="/monitoring/notification-on" role="button">Включить оповещения</a>
        <h6>Отключено: <?= $isNotificationsOff ?><br>Авто включение: <?= $isNotificationsOn ?></h6>
    <?php endif; ?>
    <?php if ($isNotificationsRunning) : ?>
        <div><code>Скрипт работает</code></div>
    <?php endif; ?>
</div>

<div class="well text-center" style="width: 500px;">
    <?php if ($isEnabledRecalcWhenEditBill) : ?>
        <a class="btn btn-primary" href="/monitoring/recalculation-balance-when-bill-edit-off" role="button">Отключить пересчет при редактировании счета</a>
    <?php else: ?>
        <a class="btn btn-info" href="/monitoring/recalculation-balance-when-bill-edit-on" role="button">Включить пересчет при редактировании счета</a>
    <?php endif; ?>
</div>

<hr>
<div class="well" style="width: 500px;">
    <fieldset>
        <label>Кеш</label>
        <form action="/bill/publish/cache" method="post">
            <div class="col-sm-12">
                <div class="col-sm-8">
                    <?php
                    echo Html::checkboxList(
                        'tags',
                        null,
                        \app\classes\helpers\DependecyHelper::LIST_TAGS
                    );
                    ?>
                </div>
                <div class="col-sm-4">
                    <input type="submit" value="Сбросить кеш" class="btn btn-primary" />
                </div>
            </div>
        </form>
    </fieldset>
</div>

<div class="well text-center" style="width: 500px;">
    <a class="btn btn-primary" href="/sim/card/mass-link-danycom" role="button">Denycom. Массовая привязка SIM и У-услуг номеров</a>
</div>