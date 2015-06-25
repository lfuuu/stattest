<?php
use yii\helpers\Url;

?>
<div id="rightmenu">
    <ul>
        <li style="background: url('/images/icons/edit.gif') no-repeat 0px 6px;">
            <a href="/account/create?parentId=<?= $client->contract_id ?>">Создать доп. ЛС</a>
        </li>
    </ul>

    <ul>
        <li style="background: url('/images/icons/edit.gif') no-repeat 0px 6px;">
            <a href="/account/edit?id=<?= $client->id ?>">Редактировать ЛС</a>
        </li>
        <li style="background: url('/images/icons/envelope.gif') no-repeat 0px 6px;">
            <a href="/client/view?id=<?= $client->id ?>&action=print&data=envelope" target="_blank">Напечатать конверт</a>
        </li>
        <li style="background: url('/images/icons/contract.gif') no-repeat 0px 6px;">
            <a href="/file/list?userId=<?= $client->id ?>">Файлы</a>
        </li>
        <li style="background: url('/images/icons/printer.gif') no-repeat 0px 6px;">
            <a href="?module=newaccounts&action=make_1c_bill&tty=mounting_orders">Заказ на Установку/Монтаж</a>
        </li>
        <li style="background: url('/images/icons/add.gif') no-repeat 0px 6px;">
            <a href="?module=newaccounts&action=make_1c_bill&tty=shop_orders">Создать заказ из Магазина</a>
        </li>
        <li style="background: url('/images/icons/disable.gif') no-repeat 0px 6px;">
            <a href="?module=newaccounts&action=make_1c_bill&tty=shop_orders&is_rollback=1">Возврат товара</a>
        </li>
        <li style="background: url('/images/icons/add.gif') no-repeat 0px 6px;">
            <a href="?module=incomegoods&action=order_edit&id=">Создать заказ Поставщику</a>
        </li>
    </ul>

    <ul>
        <li>
            <a href="?module=tt&action=view_type&type_pk=2&show_add_form=true">Создать задание</a>
        </li>
        <li>
            <a href="?module=tt&action=view_type&type_pk=1&show_add_form=true">Создать заявку на поддержку</a>
        </li>
        <li>
            <a href="index.php?module=clients&action=print_yota_contract" target="_blank">Печатать договор Yota</a>
        </li>
        <li>
            <a href="/?module=clients&id=<?= $client->id ?>&sync=true">Синхронизовать с ЛК</a>
        </li>
    </ul>

    <?php/*
    <ul style="width: 100%;">
        <li style="text-align: right;">
            <span style="float: left;">Контрагент</span>
            <a href="#"
               onclick="return showHistory({ClientContragent:<?= $client->contract->contragent->id ?>}, true);">История</a>
            &nbsp;/&nbsp;
            <a href="#"
               onclick="return showVersion({ClientContragent:<?= $client->contract->contragent->id ?>}, true);">Версии</a>
        </li>
        <li style="text-align: right;">
            <span style="float: left;">Договор</span>
            <a href="#" onclick="return showHistory({ClientContract:<?= $client->contract->id ?>}, true);">История</a>
            &nbsp;/&nbsp;
            <a href="#" onclick="return showVersion({ClientContract:<?= $client->contract->id ?>}, true);">Версии</a>
        </li>
        <li style="text-align: right;">
            <span style="float: left;">ЛС</span>
            <a href="#" onclick="return showHistory({ClientAccount:<?= $client->id ?>}, true);">История</a>
            &nbsp;/&nbsp;
            <a href="#" onclick="return showVersion({ClientAccount:<?= $client->id ?>}, true);">Версии</a>
        </li>
    </ul>
    */?>
</div>

<style>
    .size {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #rightmenu{
        padding: 10px;
        position: absolute;
        background-color: rgb(247, 247, 247);
        border-radius: 4px;
        border: 1px solid rgb(146, 146, 146);
        right: 0;
        left: 10px;
        /*
        border-left: 1px solid black;
        background: #eeeeee;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        height: 100%;*/
    }

    #rightmenu ul{
        list-style: none;
        padding-left: 0px;
    }

    #rightmenu ul li{
        padding: 5px 0 5px 20px;
    }
</style>