<?php
/**
 * Выбор страны (шаг 1/3)
 *
 * @var app\classes\BaseView $this
 * @var CountryFilter $country
 */

use app\modules\nnp\column\CountryColumn;
use app\modules\nnp\filters\CountryFilter;
use app\modules\nnp2\models\ImportHistory;
use kartik\widgets\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use app\classes\grid\GridView;
use app\classes\Html;
use yii\helpers\Url;

$countries = CountryFilter::getList(true);
// unset($countries[Country::RUSSIA]);
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => $this->title = 'Импорт. Выбор страны (шаг 1/3)', 'url' => '/nnp/import/'],
    ],
]) ?>

<h2>Выберите страну</h2>
<div class="well">
    <?php
    $form = ActiveForm::begin([
        'action' => '/nnp/import/',
    ]);
    ?>

    <div class="row">
        <div class="col-sm-4">
            <?= $form->field($country, 'code')
                ->widget(Select2::class, [
                    'data' => $countries,
                ])
                ->label(false) ?>
            Россия загружается автоматически из Россвязи.<br>
            Другие страны загружаются из файлов вручную.
        </div>

        <div class="col-sm-4">
            <?= $this->render('//layouts/_submitButton', [
                'text' => 'Загрузить или выбрать файл',
                'glyphicon' => 'glyphicon-step-forward',
                'params' => [
                    'class' => 'btn btn-success',
                ],
            ]) ?>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'version',
            'value' => function (ImportHistory $model) {
                return $model->version ?? '';
            },
        ],
        [
            'attribute' => 'country',
            'class' => CountryColumn::class,
            'format' => 'html',
            'value' => function (ImportHistory $model) {
                return
                    Html::a(
                        $model->country->name,
                        Url::to([
                            '/nnp/import/step2',
                            'countryCode' => $model->country->code,
                        ])
                    );
            }
        ],
        [
            'attribute' => 'countryFile.name',
            'format' => 'html',
            'value' => function (ImportHistory $model) {
                return
                    Html::a(
                        $model->countryFile->name,
                        Url::to([
                            '/nnp/import/step3',
                            'countryCode' => $model->country->code,
                            'fileId' => $model->countryFile->id,
                        ])
                    ) .
                    $this->render('//layouts/_buttonLink', [
                        'url' => Url::to(['/nnp/import/download', 'countryCode' => $model->country->code, 'fileId' => $model->countryFile->id]),
                        'text' => '',
                        'title' => 'Скачать',
                        'glyphicon' => 'glyphicon-download',
                        'class' => 'btn-default btn-xs',
                    ]);
            },
        ],
        [
            'attribute' => 'lines_load',
            'label' => 'Строки',
            'format' => 'html',
            'value' => function (ImportHistory $model) {
                return sprintf("Строк загружено: %s<br />Строк обработано: %s", $model->lines_load, $model->lines_processed);
            },
        ],
        [
            'attribute' => 'ranges_before',
            'label' => 'Диапазоны',
            'format' => 'html',
            'value' => function (ImportHistory $model) {
                return sprintf(
                    "Было: %s<br />Выключено: %s<br />Обновлено: %s<br />Добавлено: %s",
                    $model->ranges_before,
                    ($model->ranges_before - $model->ranges_updated),
                    $model->ranges_updated,
                    $model->ranges_added
                );
            },
        ],
        [
            'attribute' => 'state',
            'value' => function (ImportHistory $model) {
                return $model->getStateName();
            },
        ],
        [
            'attribute' => 'started_at',
        ],
        [
            'attribute' => 'finished_at',
        ],
    ],
    'extraButtons' => '',
    'isFilterButton' => false,
    'floatHeader' => false,
]);

