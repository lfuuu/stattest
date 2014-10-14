<?php
use \yii\helpers\Html;

extract($this->context->getSearchData());
?>

<div style="display: inline-block;">
    <?php if ($module == 'clients' && !$clients_my && !$currentFilter): ?>
    <span style='color:red;font-weight:bold'>Все клиенты</span> |
    <?php else: ?>
    <a href='?module=clients&action=all&letter=&region=any'>Все клиенты</a> |
    <?php endif; ?>
</div>

<div style="display: inline-block;">
    <ul id="filter_menu" STYLE="width: 240px;">
        <li>Фильтр:
            <?php if (isset($filter[$currentFilter])): ?>
                <?= $filter[$currentFilter] ?>
            <?php else: ?>
                <span style="color: gray"> нет</span>
            <?php endif; ?>

            <ul>
                <?php foreach ($filter as $key => $caption): ?>
                    <li><a href='?module=clients&action=all&region=<?=Html::encode($currentRegion)?>&letter=<?=Html::encode($key)?>' <?= $currentFilter == $key ? ' style="color:red;font-weight:bold"' : ''?> ><?=Html::encode($caption)?></a></li>
                <?php endforeach; ?>
            </ul>
        </li>
    </ul>
</div>

<div style="display: inline-block;">
    <ul id="search_menu" STYLE="width: 180px;">
        <li>Регион:
            <?php if (isset($regions[$currentRegion]) && $currentRegion != 'any'): ?>
                <?=$regions[$currentRegion]?>
            <?php else: ?>
                <span style="color: gray"> ***Любой***</span>
            <?php endif; ?>

            <ul>
                <?php foreach ($regions as $key => $caption): ?>
                    <li><a href='?module=clients&action=all&region=<?=Html::encode($key)?>&letter=<?=Html::encode($currentFilter)?>' <?= $currentRegion == $key ? ' style="color:red;font-weight:bold"' : ''?> ><?=Html::encode($caption)?></a></li>
                <?php endforeach; ?>
            </ul>
        </li>
    </ul>
</div>

<div style="display: inline-block;">
    <?php if ($module == 'clients' && $clients_my): ?>
    | <span style='color:red;font-weight:bold'>Мои клиенты</span>
    <?php else: ?>
    | <a href='?module=clients&subj=<?=Html::encode($client_subj)?>&action=my'>Мои клиенты</a>
    <?php endif; ?>
</div>

<form action="?module=clients&action=all" method=get id=searchform name=searchform style="margin-top: 5px">
    Поиск:
    <input type=hidden name=module value=clients><input type=hidden name=action value=all><input type=hidden name=smode value=1>
    <input type=text name=search class=text id=searchfield onblur='doHide()' onkeyup="doLoadUp(700)" value=''>

    <div id="searchform-buttons-extended" style="display: none">
        <input type=submit class=button value='Искать' onclick='document.getElementById("searchform").smode.value=5; return true;'>
        <input type=submit class=button value='по телефону' onclick='document.getElementById("searchform").smode.value=2; return true;'>
        <input type=submit class=button value='по voip' onclick='document.getElementById("searchform").smode.value=7; return true;'>
        <input type=submit class=button value='IP-адресу' onclick='document.getElementById("searchform").smode.value=3; return true;'>
        <input type=submit class=button value='по адресу' onclick='document.getElementById("searchform").smode.value=4; return true;'>
        <input type=submit class=button value='по email' onclick='document.getElementById("searchform").smode.value=6; return true;'>
        <input type=submit class=button value='по домену' onclick='document.getElementById("searchform").smode.value=8;return true;'>
        <input type=submit class=button value='ИНН' onclick='document.getElementById("searchform").smode.value=9; return true;'>
        <input type=submit class=button value='Счёт/Заявка' onclick='document.getElementById("searchform").module.value="newaccounts"; document.getElementById("searchform").action.value="search"; return true;'>
        <span style="border: 1px solid #ededed; color: #cdcdcd; padding: 2px 2px 2px 2px; cursor: pointer" onclick="setSearchFormSimpleMode()">&lt;&lt;</span>
    </div>

    <div id="searchform-buttons-simple" style="display: none">
        <input type=submit class=button value='Искать'>
        <span style="border: 1px solid #ededed; color: #cdcdcd; padding: 2px 2px 2px 2px; cursor: pointer" onclick="setSearchFormExtendedMode()">&gt;&gt;</span>
    </div>

</form>
<div class=text onclick='clearTimeout(timeout2)' style='display: none;position:absolute; margin-left:46px; floating:true; padding:5 5 5 5; width:600px; height:200px' id='variants'></div>

<script>
    $( "#filter_menu" ).menu({ position: { my: "left top", at: "right-201 top+15" } });
    $( "#search_menu" ).menu({ position: { my: "left top", at: "right-130 top+15" } });

    onSearchFormShow();

    function setSearchFormExtendedMode(){
        localStorage.setItem('search-form-simple-mode', 'false');
        onSearchFormShow();
    }

    function setSearchFormSimpleMode(){
        localStorage.setItem('search-form-simple-mode', 'true');
        onSearchFormShow();
    }

    function onSearchFormShow(){
        if ('true' === localStorage.getItem('search-form-simple-mode')) {
            document.getElementById('searchform-buttons-extended').style.display = 'none';
            document.getElementById('searchform-buttons-simple').style.display = 'inline';
        } else {
            document.getElementById('searchform-buttons-simple').style.display = 'none';
            document.getElementById('searchform-buttons-extended').style.display = 'inline';
        }
    }
</script>

