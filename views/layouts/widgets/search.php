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
        <div class="col-sm-3">
            <div class="input-group">
                <input id="search" type="text" class="form-control input-sm" placeholder="Search ..." name="search"
                       value="<?= (isset($request['search'])) ? $request['search'] : '' ?>">
                <span class="input-group-btn" title="Submit">
                <button type="submit" class="btn btn-default btn-sm"><i class="glyphicon glyphicon-search"></i></button>
            </span>
            </div>
        </div>
        <div class="col-sm-9">
            <div class="btn-group" id="btn-options">
                <button type="submit" class="btn btn-link btn-xs">Искать по</button>
                <button type="submit" class="btn btn-primary btn-xs" data-search="clients"
                        data-placeholder="№ ЛС или Названию">Клиент
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="contractNo"
                        data-placeholder="Номеру договора">Договор
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="inn"
                        data-placeholder="ИНН">ИНН
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="voip"
                        data-placeholder="номеру">Voip
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="email"
                        data-placeholder="email" title="Контакты email / телефон / факс">Email/тел
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="troubles"
                        data-placeholder="№ заявки">Заявк
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="bills"
                        data-placeholder="№ счёта">Счет
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="ip"
                        data-placeholder="IP адресу">IP
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="address"
                        data-placeholder="адресу">Адрес
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="domain"
                        data-placeholder="домену">Домен
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="adsl"
                        data-placeholder="ADSL">ADSL
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="troubleText"
                        data-placeholder="Текст заявки" title="Текст заявки">Текст з
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="troubleComment"
                        data-placeholder="Комментарий к заявке" title="Комментарий к заявке">Комент к з
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="sip"
                        data-placeholder="Поиск по номеру SIP-учетки" title="Поиск по номеру SIP-учетки">SIP
                </button>
                <button type="submit" class="btn btn-default btn-xs" data-search="roistat_visit"
                        data-placeholder="roistat visit" title="Поиск по roistat-visit заявки">RS
                </button>
            </div>
        </div>
    </form>
</div>
