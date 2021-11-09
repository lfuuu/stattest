<?php use app\classes\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

if ($event && !$error && $progressStyle != 'success'): ?>
    <script>
        setTimeout(function () {
            window.location.reload(false)
        }, 4000);
    </script>

<?php

endif;

echo Breadcrumbs::widget([
    'links' => [
        'Бухгалтерия',
        ['label' => 'Массовые операции', 'url' => Url::toRoute(['/bill/publish/index'])],
        ['label' => 'Создание с/ф за текущий месяц', 'url' => Url::toRoute(['/bill/bill/mass-invoices'])]
    ],
]);


if ($event) {

    if ($event->status == \app\models\EventQueue::STATUS_OK) {
        echo Html::a('Формирование с/ф закончено', Url::to(['/monitoring/event-queue', 'EventQueueFilter' => ['id'=>28381217]]), ['style' => "color: darkgreen; font-weight: bold;"]);
        echo '<br><br>';

        echo Html::a(Html::tag('div', '', ['class' => 'glyphicon glyphicon-plane']) . '&nbsp;Запустить создание с/ф', Url::to(['/bill/bill/mass-invoices', 'doCreate' => true]));
    }elseif (!$countAll) {
        echo '<b style="color: orange;">Задача стартует...</b>';
    } else {

    ?>

    <b><?= $progressValue ?>%</b> (<?= $count ?> из <?= $countAll ?>)<br>
    <?php

    if ($error) {
        $html = "Ошибка: " . $error;
    } else {
        $html = '<div class="progress">
    <div class="progress-bar progress-bar-' . $progressStyle . ' progress-bar-striped" role="progressbar" aria-valuenow="' . $progressValue . '"
         aria-valuemin="0" aria-valuemax="100" style="width:' . $progressValue . '%">
    </div>
</div>';
    }

    echo $html;

    if ($progressStyle == 'success') {
        echo Html::beginTag('h2') . 'Готово' . Html::endTag('h2');
    }
    }
} else {
    echo Html::a(Html::tag('div', '', ['class' => 'glyphicon glyphicon-plane']) . '&nbsp;Запустить создание с/ф', Url::to(['/bill/bill/mass-invoices', 'doCreate' => true]));
}
