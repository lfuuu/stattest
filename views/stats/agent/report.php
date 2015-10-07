<?php

?>


<div class="row">
    <div class="col-sm-12">
        <h2>Отчет по агентам</h2>
    </div>
    <div class="col-sm-12"></div>
    <div class="col-sm-12"></div>
    <div class="col-sm-12"></div>
    <div class="col-sm-12"></div>
    <div class="col-sm-12"></div>

    <div class="col-sm-12">
        <h3>Отчет по подключенным клиентам</h3>
    </div>
    <div class="col-sm-12">
        <div class="row" style="background: lightyellow">
            <div class="col-sm-2">Наименование клииента</div>
            <div class="col-sm-1">Дата регистрации клиента</div>
            <div class="col-sm-1">Услуга</div>
            <div class="col-sm-2">Тариф</div>
            <div class="col-sm-1">Дата включения услуги</div>
            <div class="col-sm-2">Дата и сумма 1го платежа</div>
            <div class="col-sm-3">
                <div class="row">
                    <div class="col-sm-4">Разовое</div>
                    <div class="col-sm-4">% от абонентской платы</div>
                    <div class="col-sm-4">% от превышения</div>
                </div>
            </div>
        </div>
        <?php foreach($clients as $client): ?>
        <div class="row">
            <div class="col-sm-2"><a href="/client/view?id=<?= $client->id ?>"><?= $client->contract->contragent->name; ?></a></div>
            <div class="col-sm-1"><?= $client->created ?></div>
            <div class="col-sm-1"><?= $client->usage->usage_type_name  ?></div>
            <div class="col-sm-2"><?= $client->usage->tariff_name ?></div>
            <div class="col-sm-1"><?= $client->usage->start_time ?></div>
            <div class="col-sm-2"><?= $client->usage->first_payment_date ?></div>
            <div class="col-sm-3">
                <div class="col-sm-4"><?= $client->usage->partner_sum ?></div>
                <div class="col-sm-4">% от абонентской платы</div>
                <div class="col-sm-4">% от превышения</div>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="row" style="background: lightyellow">
            <div class="col-sm-2"><b>Итого</b></div>
            <div class="col-sm-1"></div>
            <div class="col-sm-1"></div>
            <div class="col-sm-2"></div>
            <div class="col-sm-1"></div>
            <div class="col-sm-2"></div>
            <div class="col-sm-3"></div>
        </div>
    </div>
</div>
