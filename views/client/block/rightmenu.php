<?php
    use yii\helpers\Url;
?>

<h2 style="margin: 0 auto 10px; text-align: center;">ЛС № <?= $client->id ?></h2>

<a href="/client/edit?id=<?= $activeClient->id ?>"
   title="Редактировать лицевой счет">
    <img class="icon" src="/images/icons/edit.gif">Редактировать ЛС
</a>
<br><br>
<a href="/client/create?parentId=<?=$activeClient->contract_id ?>"
   title="Создать доп. лицевой счет">
    <img class="icon" src="/images/icons/edit.gif">Создать доп. ЛС
</a>
<br><br>
<a href="index.php?module=clients&id=<?= $activeClient->id ?>&action=print&data=envelope"
   target="_blank">
    <img class="icon" src="/images/icons/envelope.gif">Напечатать конверт
</a>
<br><br>
<a href="index.php?module=clients&action=files&cid=<?= $activeClient->id ?>">
    <img class="icon" src="/images/icons/contract.gif">Файлы
</a> (0 шт.)
<br><br>


<br>
<br>

<br><br>
<a href="?module=newaccounts&action=make_1c_bill&tty=mounting_orders">
    <img src="./images/icons/printer.gif" border="0">
    Создать заказ на Установку/Монтаж</a>
<br><br>
<a href="?module=newaccounts&action=make_1c_bill&tty=shop_orders"><img src="./images/icons/add.gif" border="0">Создать заказ из Магазина</a>
<br><br>
<a href="?module=newaccounts&action=make_1c_bill&tty=shop_orders&is_rollback=1">
    <img src="./images/icons/disable.gif" border="0"> Возврат товара
</a>
<br><br>

<br>
<a href="?module=incomegoods&action=order_edit&id=">
    <img src="./images/icons/add.gif" border="0">Создать заказ Поставщику
</a>
<br><br><br>

<a href="?module=tt&action=view_type&type_pk=2&show_add_form=true">Создать задание</a>
<br><br>
<a href="?module=tt&action=view_type&type_pk=1&show_add_form=true">Создать заявку на поддержку</a>

<br><br>
<a href="index.php?module=clients&action=print_yota_contract" target="_blank" id="YotaContractLink" style="display: block;">Печатать договор Yota</a>

<script type="text/javascript">
    if (!document.all && window.navigator.appName != "Opera")
        document.getElementById('YotaContractLink').style.display = 'block';
</script>

<br> <a href="./?module=clients&id=<?= $activeClient->id ?>&sync=true">Синхронизовать с ЛК</a>