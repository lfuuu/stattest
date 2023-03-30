<?php

use kartik\typeahead\TypeaheadAsset;
use yii\helpers\Url;

TypeaheadAsset::register(Yii::$app->getView());
$request = Yii::$app->request->get();
?>

<div>
    <form action="<?= Url::toRoute(['/search/index', 'search' => isset($request['search']) ? $request['search'] : '']) ?>"
          id="search-form">
        <input type="hidden" name="searchType"
               value="<?= (isset($request['searchType'])) ? $request['searchType'] : '' ?>"
               id="search-type">

        <div class="col-sm-4">
            <div class="input-group">
                <input id="search" type="text" class="form-control input-sm" placeholder="Search ..." name="search"
                       value="<?= (isset($request['search'])) ? $request['search'] : '' ?>">
                <span class="input-group-btn" title="Submit">
                <button id="search_btn" type="submit" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-search"></i></button>
            </span>
            </div>
        </div>

        <div class="col-sm-8">
            <div class="dropdown" id="btn-options">
                <button type="submit" class="btn btn-link btn-xs">Искать по</button>

                <button id="title_search" class="btn btn-primary dropdown-toggle btn-xs" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    Клиенту
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" id="search-options" aria-labelledby="dropdownMenu1">
                    <li><a href="#" data-search="clients" title="№ ЛС или Названию">Клиенту</a></li>
                    <li><a href="#" data-search="contractNo" title="Номеру договора">Договор</a></li>
                    <li><a href="#" data-search="inn">ИНН</a></li>
                    <li><a href="#" data-search="voip">Voip</a></li>
                    <li><a href="#" data-search="email" title="Контакты email / телефон / факс">Email/тел</a></li>
                    <li><a href="#" data-search="troubles" title="№ заявки">Заявка</a></li>
                    <li><a href="#" data-search="bills" title="№ счёта">Счет</a></li>
                    <li><a href="#" data-search="ip">IP адресу</a></li>
                    <li><a href="#" data-search="address">Адресу</a></li>
                    <li><a href="#" data-search="domain">Домену</a></li>
                    <li><a href="#" data-search="adsl">ADSL</a></li>
                    <li><a href="#" data-search="troubleText">Текст заявки</a></li>
                    <li><a href="#" data-search="troubleComment">Комментарий к заявке</a></li>
                    <li><a href="#" data-search="sip">Поиск по SIP-учетке</a></li>
                    <li><a href="#" data-search="roistat_visit">Поиск по roistat-visit заявки</a></li>
                </ul>
            </div>
        </div>
    </form>
</div>
