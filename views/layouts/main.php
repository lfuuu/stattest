<?php
use yii\helpers\Html;
use app\assets\AppAsset;
/* @var $this \yii\web\View */
/* @var $user \app\models\User */

global $fixclient_data;

AppAsset::register($this);
$user = Yii::$app->user->identity;
$myTroublesCount = $this->context->getMyTroublesCount();
$activeClient = \app\models\ClientAccount::findOne($fixclient_data['id']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <base href="/" />
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>

<?php if (Yii::$app->user->can('monitoring.top')):?>
    <iframe src='?module=monitoring&action=top' width=100% height=17 style='border:0; padding:0 0 0 0;margin:-15 0 0 0;'></iframe>
<?php endif; ?>

<style>

    .i-manager{
        color: #43657d;
    }

    .i-accmanager{
        color: #7b217d;
    }

    i.check{
        color:green;
        font-size:16px;
        font-style: normal;
    }

    i.check:before{
        content: '✓';
    }

    i.uncheck{
        color:red;
        font-size:16px;
        font-style: normal;
    }

    i.uncheck:before{
        content: '✕';
    }
</style>

<div class="row" style="background: white;
  position: fixed;
  top: 0;
  z-index: 999;
  border-bottom: 1px solid black;
  box-shadow: 0 0 10px rgba(0,0,0,0.5);
  padding-bottom: 10px;
  ">
    <div class="col-sm-12">
        <div class="row" style="width: 300px; float: left; ">
            <div class="col-sm-6">
                <a href="/" class="logo"></a>
            </div>
            <div class="col-sm-6">
                <div style="padding-top: 15px; text-align: center;">
                    <?php if ($myTroublesCount > 0): ?>
                        <div class="menupanel" style="text-align: center">
                            <a><?=$user->name?></a><br>
                            <a href="/site/logout">Выйти</a><br>
                            <a href="/?module=tt&action=list2&mode=2" style="font-weight: bold; color: #a00000; font-size: 12px;">Поручено <?=$myTroublesCount?> заявок</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div id="top_search" style="margin-top: 15px; height: 40px; padding-left: 310px;">
            <?php if (Yii::$app->user->can('clients.read')): ?>
                <?= $this->render('widgets/search') ?>
            <?php endif; ?>
            <?php if($activeClient):?>
                <div class="row">
                    <div class="col-sm-12">
                        <h2 style=" display: inline-block; margin: 0; font-weight: normal; " class="c-blue-color">
                            <a href="/client/view?id=<?= $activeClient->id ?>">
                                <?=$activeClient->contract->contragent->name_full?> / Договор № <?=$activeClient->contract->number?> / ЛС № <?=$activeClient->id?>
                            </a>
                        </h2>
                        &nbsp;
                        <a href="/account/unfix" title="Снять"><i class="uncheck"></i> </a>
                    </div>
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
    <div style="min-height: 70%">

    <?= $this->render('widgets/messages') ?>

    <?= $content ?>

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
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
            'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
            'Июл','Авг','Сен','Окт','Ноя','Дек'],
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        weekHeader: 'Не',
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
        showMonthAfterYear: false,
        yearSuffix: ''};
    $(document).ready(function(){
        $('.select2').select2();
        $.datepicker.setDefaults(datepicker_ru);
        $('.datepicker').datepicker();
    });
</script>

</body>
</html>
<?php $this->endPage() ?>
