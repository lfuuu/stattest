<?php use app\classes\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

if (!$error && $progressStyle != 'success'): ?>
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
])
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