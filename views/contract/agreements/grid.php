<?php

/** @var \app\forms\client\ContractEditForm $contract */
/** @var array $docs */
/** @var \app\classes\BaseView $baseView */

use app\classes\grid\GridView;
use app\classes\Html;
use kartik\grid\ActionColumn;
use yii\data\ArrayDataProvider;
use app\models\ClientDocument;

$emptyDocument = new ClientDocument;

$dataProvider = new ArrayDataProvider([
    'allModels' => $docs,
    'sort' => false,
    'pagination' => false,
]);
?>

<div class="clearfix"></div><br />

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => ActionColumn::className(),
            'template' => '{edit} {print} {send} {is_disable} {link}',
            'buttons' => [
                'edit' => function ($url, $model) {
                    return Html::a(
                        Html::img('/images/icons/edit.gif', ['class' => 'icon']),
                        ['/document/edit', 'id' => $model->id],
                        [
                            'target' => '_blank',
                            'title' => 'Редактировать доп. соглашение',
                        ]
                    );
                },
                'print' => function ($url, $model) {
                    return Html::a(
                        Html::img('/images/icons/printer.gif', ['class' => 'icon']),
                        ['/document/print', 'id' => $model->id],
                        [
                            'target' => '_blank',
                            'title' => 'Распечатать доп. соглашение',
                        ]
                    );
                },
                'send' => function ($url, $model) {
                    return Html::a(
                        Html::img('/images/icons/envelope.gif', ['class' => 'icon']),
                        ['/document/send', 'id' => $model->id],
                        [
                            'target' => '_blank',
                            'title' => 'Отправить доп. соглашение',
                        ]
                    );
                },
                'is_disable' => function ($url, $model) {
                    if ($model->is_active) {
                        return Html::a(
                            Html::img('/images/icons/delete.gif', ['class' => 'icon']),
                            ['document/activate', 'id' => $model->id],
                            [
                                'title' => 'Отключить доп. соглашение',
                            ]
                        );
                    }

                    return Html::a(
                        Html::img('/images/icons/add.gif', ['class' => 'icon']),
                        ['document/activate', 'id' => $model->id],
                        [
                            'title' => 'Активировать доп. соглашение',
                        ]
                    );
                },
                'link' => function ($url, $model) {
                    return Html::a(
                        Html::img('/images/icons/contract.gif', ['class' => 'icon']),
                        ['/document/print-by-code', 'code' => $model->link],
                        [
                            'target' => '_blank',
                            'title' => 'Ссылка на доп. соглашение',
                        ]
                    );
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '150px'
        ],
        [
            'format' => 'raw',
            'value' => function ($data) {
                return (
                $data->is_active ?
                    Html::tag('span', '', [
                        'class' => 'glyphicon glyphicon-ok text-success',
                        'title' => 'Активное доп. соглашение',
                    ]) :
                    ''
                );
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '50px',
        ],
        [
            'attribute' => 'contract_no',
            'width' => '20%',
            'label' => $emptyDocument->getAttributeLabel('contract_no'),
        ],
        [
            'attribute' => 'contract_date',
            'width' => '10%',
            'label' => $emptyDocument->getAttributeLabel('contract_date'),
        ],
        [
            'attribute' => 'comment',
            'label' => $emptyDocument->getAttributeLabel('comment'),
        ],
        [
            'attribute' => 'user',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->user->name;
            },
            'width' => '15%',
            'label' => $emptyDocument->getAttributeLabel('user'),
        ],
        [
            'attribute' => 'ts',
            'width' => '10%',
            'hAlign' => GridView::ALIGN_CENTER,
            'label' => $emptyDocument->getAttributeLabel('ts'),
        ]
    ],
    'floatHeader' => false,
    'panel' => [
        'footer' => false,
        'before' => $this->render('add', [
            'firstAgreementNo' => reset($docs)->contract_no,
            'contract' => $contract,
        ]),
    ],
    'panelHeadingTemplate' => '
        <div class="pull-left col-sm-4">
            <b>Доп. соглашения</b> {summary}
        </div>
        <div class="clearfix"></div>
    ',
]);