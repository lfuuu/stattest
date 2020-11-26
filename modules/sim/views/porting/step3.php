<?php
/**
 * Импорт данных из БДПН выполнен
 *
 * @var \app\classes\BaseView $this
 * @var int $count
 */

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        ['label' => 'Портирование отчёт', 'url' => $homeUrl = '/sim/porting/'],
        ['label' => 'Импорт данных из БДПН', 'url' => $cancelUrl = '/sim/porting/import/'],
        $this->title = 'Импорта данных из БДПН выполнен'
    ],
]) ?>

<h2>Результат импорта csv-файла для портирования</h2>
<div class="well">
    <div class="form-group">
        <?php
            $link = Html::a('Посмотреть результаты', $homeUrl);
            echo Html::tag('div', '<strong>Успешно импортировано ' . $count . ' строк.</strong> ' . $link, ['class' => 'alert alert-success']);
        ?>
    </div>
</div>