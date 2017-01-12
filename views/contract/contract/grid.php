<?php

/** @var \app\forms\client\ContractEditForm $contract */
/** @var array $docs */
/** @var \app\classes\BaseView $baseView */

use app\classes\grid\GridView;
use app\classes\Html;
use app\models\ClientContract;
use app\models\ClientDocument;
use kartik\grid\ActionColumn;
use yii\data\ArrayDataProvider;

$emptyDocument = new ClientDocument;
$hasContract = count($docs);

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
            'template' => '{data}',
            'buttons' => [
                'data' => function ($url, $data) use ($contract) {
                    /** @var \app\models\ClientDocument $data */
                    $result = Html::tag('b', 'Не создан');

                    if (!empty($data->getFileContent())) {
                        $result = '';

                        if ($contract->state == ClientContract::STATE_UNCHECKED) {
                            $result .= Html::a(
                                Html::img('/images/icons/edit.gif', ['class' => 'icon']),
                                ['/document/edit', 'id' => $data->id],
                                [
                                    'target' => '_blank',
                                    'title' => 'Редактировать',
                                ]
                            );
                        }

                        $result .= Html::a(
                            Html::img('/images/icons/printer.gif', ['class' => 'icon']),
                            ['/document/print/', 'id' => $data->id],
                            [
                                'target' => '_blank',
                                'title' => 'Распечатать',
                            ]
                        );

                        $result .= Html::a(
                            Html::img('/images/icons/envelope.gif', ['class' => 'icon']),
                            ['/document/send', 'id' => $data->id],
                            [
                                'target' => '_blank',
                                'title' => 'Отправить',
                            ]
                        );

                        if ($contract->state == ClientContract::STATE_UNCHECKED) {
                            if ($data->is_active) {
                                $result .= Html::a(
                                    Html::img('/images/icons/delete.gif', ['class' => 'icon']),
                                    ['document/activate', 'id' => $data->id],
                                    [
                                        'title' => 'Отключить',
                                    ]
                                );
                            } else {
                                $result .= Html::a(
                                    Html::img('/images/icons/add.gif', ['class' => 'icon']),
                                    ['document/activate', 'id' => $data->id],
                                    [
                                        'title' => 'Активировать',
                                    ]
                                );
                            }
                        }

                        $result .= Html::a(
                            Html::img('/images/icons/contract.gif', ['class' => 'icon']),
                            ['/document/print-by-code', 'code' => $data->link],
                            [
                                'target' => '_blank',
                                'title' => 'Ссылка',
                            ]
                        );
                    }

                    return $result;
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
                            'title' => 'Активный договор',
                        ]) :
                        ''
                );
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '50px',
        ],
        [
            'attribute' => 'is_external',
            'value' => function ($data) {
                return ClientContract::$externalType[$data->contract->is_external];
            },
            'hAlign' => GridView::ALIGN_CENTER,
            'width' => '10%',
            'label' => $emptyDocument->getAttributeLabel('is_external'),
        ],
        [
            'attribute' => 'contract_no',
            'width' => '20%',
            'label' => $emptyDocument->getAttributeLabel('contract_no'),
        ],
        [
            'attribute' => 'contract_date',
            'hAlign' => GridView::ALIGN_CENTER,
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
        ],
    ],
    'panel' => [
        'heading' => 'Договор',
        'footer' => false,
        'before' => $this->render('add', [
            'contract' => $contract,
            'hasContract' => $hasContract,
        ]),
    ],
    'panelHeadingTemplate' => '
        <div class="pull-left col-sm-4">
            <b>Договор</b> {summary}
        </div>
        <div class="clearfix"></div>
    ',
]);