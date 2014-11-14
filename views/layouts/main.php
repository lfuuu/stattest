<?php
use yii\helpers\Html;
use app\assets\AppAsset;
/* @var $this \yii\web\View */
/* @var $user \app\models\User */

global $fixclient_data;

AppAsset::register($this);

$user = Yii::$app->user->identity;

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

<div class="layout_left">
    <div class="site_caption">
        <a href="/" class="logo"></a>
        <div class="message">Сервер статистики</div>

        <?php if ($fixclient_data): ?>
            <script>var fixclient="<?=$fixclient_data['client']?>";</script>
            <?php if (!Yii::$app->user->can('clients.read')): ?>
                <b><?= Html::encode($fixclient_data['client']) ?></b>
                <div class=card>
                    <?= Html::a('Logout', ['site/logout']) ?>
                </div>
            <?php else: ?>
                <div style="padding: 2px">
                    <b><a href="?module=clients&id=<?=$fixclient_data['client']?>">
                            <?=
                            $fixclient_data['client']
                                ? Html::encode($fixclient_data['client'])
                                : '<font color=red>id=</font>' . Html::encode($fixclient_data['id'])
                            ?>
                        </a></b>
                    (<b><a href='?module=clients&unfix=1'>снять</a></b>)
                </div>
                <div style="padding: 2px">
                    <?=  Html::encode($fixclient_data['company']);?>
                </div>
                <div class=card>
                    Логин: <strong><?=Html::encode($user->user)?></strong>
                    <?= Html::a('Logout', ['site/logout']) ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <script>var fixclient = "";</script>
            Клиент не выбран
            <div class=card>
                Логин: <strong><?=$user->user?></strong>
                <?= Html::a('Logout', ['site/logout']) ?>
            </div>
        <?php endif; ?>

        <iframe id=toggle_frame src='?module=usercontrol&action=ex_toggle' height=1 width=1 style='display:none'></iframe>
    </div>

    <?= $this->render('widgets/left_menu', ['user' => $user]); ?>
</div>

<div class="layout_main">
    <div style="margin-top: 15px; margin-bottom: 40px">
        <?php if (Yii::$app->user->can('clients.read')): ?>
            <?= $this->render('widgets/search') ?>
        <?php endif; ?>
    </div>

    <?= $this->render('widgets/messages') ?>

    <?= $content ?>

</div>

<div style="clear: left"></div>

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
        $.datepicker.setDefaults(datepicker_ru);
        $('.datepicker').datepicker();
        $('.select2').select2();
    });
</script>

</body>
</html>
<?php $this->endPage() ?>
