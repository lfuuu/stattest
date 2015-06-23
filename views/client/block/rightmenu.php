<?php
use yii\helpers\Url;

?>
<div id="rightmenu">
    <ul>
        <li>
            <a href="/account/create?parentId=<?= $client->contract_id ?>" title="Создать доп. лицевой счет">
                <img class="icon" src="/images/icons/edit.gif">Создать доп. ЛС
            </a>
        </li>
    </ul>

    <ul>
        <li>
            <a href="/account/edit?id=<?= $client->id ?>" title="Редактировать лицевой счет">
                <img class="icon" src="/images/icons/edit.gif">Редактировать ЛС
            </a>
        </li>
        <li>
            <a href="/client/view?id=<?= $client->id ?>&action=print&data=envelope" target="_blank">
                <img class="icon" src="/images/icons/envelope.gif">Напечатать конверт
            </a>
        </li>
        <li>
            <a href="/file/list?userId=<?= $client->id ?>">
                <img class="icon" src="/images/icons/contract.gif">Файлы
            </a>
        </li>
        <li>
            <a href="?module=newaccounts&action=make_1c_bill&tty=mounting_orders">
                <img src="./images/icons/printer.gif" border="0">
                Создать заказ на Установку/Монтаж
            </a>
        </li>
        <li>
            <a href="?module=newaccounts&action=make_1c_bill&tty=shop_orders"><img src="./images/icons/add.gif"
                                                                                   border="0">Создать заказ из Магазина</a>
        </li>
        <li>
            <a href="?module=newaccounts&action=make_1c_bill&tty=shop_orders&is_rollback=1">
                <img src="./images/icons/disable.gif" border="0"> Возврат товара
            </a>
        </li>
        <li>
            <a href="?module=incomegoods&action=order_edit&id=">
                <img src="./images/icons/add.gif" border="0">Создать заказ Поставщику
            </a>
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
        /*
        border-left: 1px solid black;
        background: #eeeeee;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
        height: 100%;*/
    }

    #rightmenu ul{
        list-style: none;
        padding-left: 5px;
    }

    #rightmenu ul li{
        padding: 5px 0;
    }
</style>