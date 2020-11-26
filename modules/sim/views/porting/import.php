<?php
/**
 * Импорт данных из БДПН
 *
 * @var \app\classes\BaseView $this
 */

use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        ['label' => 'Портирование отчёт', 'url' => $cancelUrl = '/sim/porting/'],
        $this->title = 'Импорт данных из БДПН'
    ],
]) ?>

<h2>Загрузить csv-файл для портирования</h2>
<div class="well">
    <?= $this->render('_import_help') ?>

    <?php
    $form = ActiveForm::begin([
        'options' => [
            'enctype' => 'multipart/form-data',
        ],
    ]);
    ?>

    <?php // Выбор файла для загрузки ?>
    <div class="form-group">
        <input type="file" name="file" accept="text/csv,application/zip"/>
        <?= $this->render('//layouts/_toggleButton', ['divSelector' => '#step2-upload-help', 'title' => 'Формат файла']) ?>
    </div>

    <?= $this->render('//layouts/_submitButton', [
        'text' => 'Загрузить',
        'glyphicon' => 'glyphicon-upload',
        'params' => [
            'class' => 'btn btn-success',
        ],
    ]) ?>

    <?php ActiveForm::end(); ?>
</div>