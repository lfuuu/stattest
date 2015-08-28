<?php
use yii\helpers\Html;
use app\assets\AppAsset;

/* @var $this \yii\web\View */
/* @var $user \app\models\User */

global $fixclient_data;

AppAsset::register($this);
$user = Yii::$app->user->identity;
$myTroublesCount = $this->context->getMyTroublesCount();
if (isset($fixclient_data['id'])) {
    $activeClient = \app\models\ClientAccount::findOne($fixclient_data['id']);
} else {
    $activeClient = null;
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <base href="/"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>

<?php if (Yii::$app->user->can('monitoring.top')): ?>
    <iframe src='?module=monitoring&action=top' width=100% height=17
            style='border:0; padding:0 0 0 0;margin:-15 0 0 0;'></iframe>
<?php endif; ?>

<style>

    .i-manager {
        color: #43657d;
    }

    .i-accmanager {
        color: #7b217d;
    }

    i.check {
        color: green;
        font-size: 16px;
        font-style: normal;
    }

    i.check:before {
        content: '✓';
    }

    i.uncheck {
        color: red;
        font-size: 16px;
        font-style: normal;
    }

    i.uncheck:before {
        content: '✕';
    }
</style>

<div class="row" style="background: white;
  position: fixed;
  top: 0;
  z-index: 999;
  border-bottom: 1px solid black;
  box-shadow: 0 0 10px rgba(0,0,0,0.5);
  padding-bottom: 8px;
  left:0;
  right:0;
  ">
    <div class="col-sm-12">
        <div class="row" style="width: 350px; float: left; ">
            <div class="col-sm-6">
                <a href="/" class="logo"></a>
            </div>
            <div class="col-sm-6">
                <div style="padding-top: 15px; text-align: center;">
                        <div class="menupanel" style="text-align: center">
                            <a href="#" onclick="$('.user-menu').toggle(); $('.user-menu').closest('.menupanel').toggleClass('active-link-client'); return false;"><?= $user->name ?></a>
                            <ul class="user-menu" style="display: none;">
                                <li><a href="#" onclick="$('.user-menu').toggle(); $('.user-menu').closest('.menupanel').toggleClass('active-link-client'); return false;"><?= $user->name ?></a></li>
                                <li><a href="/?module=usercontrol&action=edit">Изменить профайл</a></li>
                                <li><a href="/?module=usercontrol&amp;action=edit_pass">Изменить пароль</a></li>
                                <li><a href="/site/logout">Выход</a></li>
                            </ul>
                            <?php if ($myTroublesCount > 0): ?>
                                <br>
                                <br>
                                <a href="/?module=tt&action=list2&mode=2" style="font-weight: bold; color: #a00000; font-size: 12px;">
                                    Поручено <?= $myTroublesCount ?> заявок
                                </a>
                            <?php endif; ?>
                        </div>
                </div>
            </div>
        </div>
        <div id="top_search" style="margin-top: 15px; height: 40px; padding-left: 360px;">
            <?php if (Yii::$app->user->can('clients.read')): ?>
                <div class="row">
                    <?= $this->render('widgets/search') ?>
                    <?php if ($activeClient): ?>
                        <div class="col-sm-12">
                            <?php
                            $str = htmlspecialchars($activeClient->contract->contragent->name .' / Договор
                            № '. $activeClient->contract->number .' / ЛС № '. $activeClient->id);
                            ?>
                            <h2 style="display: inline-block; margin: 0; font-weight: normal; margin-top: 8px;
                                    max-width: 90%;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;
                            " class="c-blue-color"
                                title="<?=$str?>">
                                <a href="/client/view?id=<?= $activeClient->id ?>#contragent<?= $activeClient->contract->contragent_id ?>">
                                    <?= $activeClient->contract->contragent->name .' / Договор № '
                                    . $activeClient->contract->number .' / ЛС № ' . "<b style=\"font-size:120%;\">{$activeClient->id}</b>" ?>
                                </a>
                            </h2>
                            <a href="/account/unfix" title="Снять" style="vertical-align: text-bottom;"><i class="uncheck"></i> </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<div class="layout_left col-sm-2">

    <?= $this->render('widgets/left_menu', ['user' => $user]); ?>

    <div style="height: 100px;"></div>
</div>

<div class="layout_main col-sm-10 col-md-push-2">
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div style="text-align: center;" class="alert alert-danger fade in">
            <div style="font-weight: bold;">
                <?= Yii::$app->session->getFlash('error'); ?>
            </div>
        </div>
    <?php endif; ?>

    <div style="min-height: 70%">

        <?= $this->render('widgets/messages') ?>

        <?= $content ?>

        <div class="row">
            <div style="padding: 15px; margin-top: 100px" class="col-sm-12">
                <a href="http://www.mcn.ru/"><img height="16" src="images/logo_msn_s.gif" width="58"
                                                  border="0/"></a><br>
                <span style="color: #666">©2014 MCN. тел. (495) 105–9999 (отдел продаж), (495) 105–9995 (техподдержка)</span>
            </div>
        </div>
    </div>
</div>

<?php $this->endBody() ?>

<script>
    LOADED = 1;

    var datepicker_ru = {
        closeText: 'Закрыть',
        prevText: '&#x3c;Пред',
        nextText: 'След&#x3e;',
        currentText: 'Сегодня',
        monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
            'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        monthNamesShort: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн',
            'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
        dayNames: ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'],
        dayNamesShort: ['вск', 'пнд', 'втр', 'срд', 'чтв', 'птн', 'сбт'],
        dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        weekHeader: 'Не',
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $(document).ready(function () {
        $('.select2').select2();
        $.datepicker.setDefaults(datepicker_ru);
        $('.datepicker').datepicker();

        $('.layout_main , .layout_left ').css('top', $('#top_search').closest('.row').height()+25);
    });
</script>

</body>
</html>
<?php $this->endPage() ?>
