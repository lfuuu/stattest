<?php

/** @var \app\forms\client\ContractEditForm $contract */
/** @var \app\classes\BaseView $baseView */

use app\models\media\ClientFiles;
use kartik\grid\ActionColumn;
use app\assets\AppAsset;
use app\classes\Html;
use app\classes\grid\GridView;
use app\classes\grid\column\universal\TagsColumn;
use app\helpers\DateTimeZoneHelper;

$this->registerCssFile('@web/css/behaviors/media-manager.css', ['depends' => [AppAsset::class]]);

$model = new ClientFiles;
$dataProvider = $model->search($contract->id);
$baseView = $this;
?>

<div class="clearfix"></div><br />

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $model,
    'columns' => [
        [
            'class' => ActionColumn::class,
            'template' => '{delete} {send}',
            'buttons' => [
                'delete' => function ($url, $model) use ($baseView) {
                    if (\Yii::$app->user->can('clients.can_delete_contract_documents')) {
                        return $baseView->render('//layouts/_actionDrop', [
                            'url' => '/file/delete-client-file?id=' . $model->id,
                        ]);
                    }

                    return '';
                },
                'send' => function ($url, $model) {
                    return Html::a(Html::img('/images/icons/envelope.gif'), '#', [
                        'class' => 'fileSend',
                        'data-id' => $model->id,
                        'title' => 'Отправить файл',
                    ]);
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => function ($file) {
                $comment = '';

                if ($file->comment) {
                    $comment = Html::tag('br') . Html::tag('label', $file->comment, ['class' => 'label label-default']);
                }

                return
                    Html::a($file->name, ['/file/get-file', 'model' => 'clients', 'id' => $file->id], ['target' => '_blank']) .
                    $comment;
            }
        ],
        [
            'attribute' => 'is_show_in_lk',
            'format' => 'raw',
            'value' => function (ClientFiles $file) {
                return Html::checkbox("client_file[{$file->id}]", $file->is_show_in_lk, [
                    'class' => 'show_client_file_in_lk',
                    'data-id' => $file->id
                ]);
            }
        ],
        [
            'class' => TagsColumn::class,
            'filter' => TagsColumn::class,
            'isEditable' => true,
            'width' => '25%',
        ],
        [
            'attribute' => 'user',
            'format' => 'raw',
            'value' => function ($file) {
                return $file->user->name;
            },
            'width' => '15%',
        ],
        [
            'attribute' => 'ts',
            'width' => '10%',
            'value' => function ($file) {
                return DateTimeZoneHelper::getDateTime($file->ts);
            },
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'floatHeader' => false,
    'panel' => [
        'footer' => false,
        'before' => $baseView->render('add', [
            'contract' => $contract,
        ]),
    ],
    'panelHeadingTemplate' => '
        <div class="pull-right">
            {filterButton}
        </div>
        <div class="pull-left col-sm-4">
            <b>Файлы</b> {summary}
        </div>
        <div class="clearfix"></div>
    ',
    'options' => [
        'class' => 'fullTable',
    ],
]);

echo $baseView->render('send', [
    'contract' => $contract,
]);