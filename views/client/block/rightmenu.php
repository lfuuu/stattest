<div id="rightmenu">
    <ul>
        <li style="background: url('/images/icons/edit.gif') no-repeat 0px 6px;">
            <a href="/account/edit?id=<?= $account->id ?>">Редактир. ЛС</a>
        </li>
        <li style="background: url('/images/icons/edit.gif') no-repeat 0px 6px;">
            <a href="/account/create?parentId=<?= $account->contract->id; ?>">Создать доп. ЛС</a>
        </li>
        <li style="background: url('/images/icons/edit.gif') no-repeat 0px 6px;">
            <a href="?module=tt&action=view_type&type_pk=2&show_add_form=true">Создать задание</a>
        </li>
        <li style="background: url('/images/icons/edit.gif') no-repeat 0px 6px;">
            <a href="?module=tt&action=view_type&type_pk=1&show_add_form=true">Создать трабл</a>
        </li>
        <li style="background: url('/images/icons/edit.gif') no-repeat 0px 6px;">
            <a href="/transfer/index/?client=<?= $account->id ?>" onclick="return showIframePopup(this)">Перенос услуг</a>
        </li>
        <li style="background: url('/images/icons/add.gif') no-repeat 0px 6px;">
            <a href="?module=newaccounts&action=make_1c_bill&tty=mounting_orders">Установка/Монтаж</a>
        </li>
        <li style="background: url('/images/icons/add.gif') no-repeat 0px 6px;">
            <a href="?module=newaccounts&action=make_1c_bill&tty=shop_orders">Заказ И-М</a>
        </li>
        <li style="background: url('/images/icons/add.gif') no-repeat 0px 6px;">
            <a href="?module=newaccounts&action=make_1c_bill&tty=shop_orders&is_rollback=1">Возврат товара</a>
        </li>
        <?php if ($account->contract->business_id == \app\models\Business::PROVIDER) : ?>
        <li style="background: url('/images/icons/disable.gif') no-repeat 0px 6px;">
            <a href="?module=incomegoods&action=order_edit&id=">Заказ Поставщику</a>
        </li>
        <?php endif; ?>
        <li style="background: url('/images/icons/printer.gif') no-repeat 0px 6px;">
            <a href="/document/print-envelope?clientId=<?= $account->id ?>" target="_blank">Конверт</a>
        </li>
        <li style="background: url('/images/icons/printer.gif') no-repeat 0px 6px;">
            <a href="/custom-print/print-client/?id=<?= $account->id ?>" onclick="return ImmediatelyPrint(this)">Карточка</a>
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
