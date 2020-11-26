<?php
/**
 * Импорт данных из БДПН превью
 *
 * @var \app\classes\BaseView $this
 * @var string $path
 * @var string $errorMessage
 * @var array $warnings
 */

use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\helpers\Html;

$readyForImport = !$errorMessage && empty($warnings);

?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'SIM-карты', 'url' => '/sim/'],
        ['label' => 'Портирование отчёт', 'url' => '/sim/porting/'],
        ['label' => 'Импорт данных из БДПН', 'url' => $cancelUrl = '/sim/porting/import/'],
        $this->title = 'Превью импорта данных из БДПН'
    ],
]) ?>

<h2>Превью импорта csv-файла для портирования</h2>
<div class="well">
    <?php
    $form = ActiveForm::begin();
    ?>

    <div class="form-group">
        <input type="hidden" name="path" value="<?= $path ?>"/>

        <?php
            if ($readyForImport) {
                echo Html::tag('div', '<strong>Файл проверен и готов к импорту.</strong>', ['class' => 'alert alert-success']);
            } else if ($warnings) {
                echo Html::tag('div', '<strong>Импорт невозможен, потому что файл содержит ошибки (' . count($warnings) . '). Исправьте ошибки в файле и загрузите его заново.</strong>', ['class' => 'alert alert-danger']);

                echo "<ul>";
                foreach ($warnings as $line => $lineData) {
                    foreach ($lineData as $field => $text) {
                        echo Html::tag(
                            'li',
                            'Строка ' . $line . ', поле <strong>' . $field . '</strong>: ' . $text,
                            ['class' => 'alert alert-danger']
                        );
                    }
                }
                echo "</ul>";
            } else if ($errorMessage) {
                echo Html::tag('div', '<strong>' . $errorMessage . '</strong>', ['class' => 'alert alert-danger']);
            } else {
                echo Html::tag('div', '<strong>Неизвестная ошибка.</strong>', ['class' => 'alert alert-danger']);
            }

        ?>
    </div>

    <?php

        if ($readyForImport) {
            echo $this->render('//layouts/_submitButton', [
                'text' => 'Импортировать',
                'glyphicon' => 'glyphicon-upload',
                'params' => [
                    'class' => 'btn btn-success',
                ],
            ]);
        } else {
            echo $this->render('//layouts/_buttonLink', [
                'url' => $cancelUrl,
                'text' => 'Загрузить другой файл',
                'glyphicon' => 'glyphicon-step-backward',
                'class' => 'btn-default',
            ]);
        }
    ?>

    <?php ActiveForm::end(); ?>
</div>