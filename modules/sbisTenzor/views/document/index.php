<?php

use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\modules\sbisTenzor\classes\SBISDocumentStatus;
use app\modules\sbisTenzor\models\SBISDocument;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use kartik\grid\ActionColumn;
use app\classes\grid\GridView;

/**
 * @var ActiveDataProvider $dataProvider
 * @var \app\classes\BaseView $baseView
 * @var int $clientId
 * @var int $state
 * @var string $title
 */

$baseView = $this;

$this->title = $title;

echo Html::formLabel($title);
echo Breadcrumbs::widget([
    'links' => [
        'СБИС',
        ['label' => $this->title = $title,],
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'attribute' => 'id',
            'label' => 'ID',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a($model->id, $model->getUrl());
            },
        ],
        [
            'attribute' => 'sbis_organization_id',
            'label' => 'Направление',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return
                    Html::a(
                        sprintf(
                            "%s >>> %s",
                            $model->sbisOrganization->organization->name,
                            $model->clientAccount->contragent->name
                        ),
                        $model->getUrl()
                    );
            },
        ],
        [
            'attribute' => 'number',
            'label' => 'Номер',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a($model->number, $model->getUrl());
            },
        ],
        [
            'attribute' => 'date',
            'label' => 'Дата',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a($model->date, $model->getUrl());
            },
        ],
        [
            'attribute' => 'comment',
            'label' => 'Комментарий',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a($model->comment, $model->getUrl());
            },
        ],
        [
            'attribute' => 'attachments',
            'label' => 'Вложений',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                return Html::a(count($model->attachments), $model->getUrl());
            },
        ],
        [
            'attribute' => 'attachments',
            'label' => 'Подписан',
            'format' => 'html',
            'value'     => function (SBISDocument $model) use ($baseView) {
                return $model->isSigned() ? $baseView->render('//layouts/_actionEnable') : $baseView->render('//layouts/_actionDisable');
            },
        ],
        [
            'attribute' => 'state',
            'format' => 'html',
            'value'     => function (SBISDocument $model) {
                $external = $model->external_state_name ? : '';
                $external = $external ? sprintf(' (%s)', $external) : '';
                return Html::a($model->stateName . $external, $model->getUrl());;
            },
        ],
        [
            'attribute' => 'created_at',
            'value'     => function (SBISDocument $model) {
                return DateTimeZoneHelper::getDateTime($model->created_at);
            },
        ],
        [
            'attribute' => 'updated_at',
            'value'     => function (SBISDocument $model) {
                return DateTimeZoneHelper::getDateTime($model->updated_at);
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{view} {start}',
            'buttons' => [
                'view' => function ($url, SBISDocument $model) use ($baseView) {
                    return $baseView->render('//layouts/_actionView', [
                        'url' => $model->getUrl(),
                    ]);
                },
                'start' => function ($url, SBISDocument $model) use ($baseView) {
                    if ($model->state == SBISDocumentStatus::CREATED) {
                        return $baseView->render('//layouts/_link', [
                            'url' => '/sbisTenzor/document/start?id=' . $model->id,
                            'glyphicon' => 'glyphicon-send text-success',
                            'params' => [
                                'onClick' => 'return confirm("Отправить данный пакет?")',
                            ],
                        ]);
                    }

                    return false;
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
    ],
    'extraButtons' =>
        $this->render('//layouts/_buttonLink', [
                'url' => '/sbisTenzor/document/' . ($clientId ? '?clientId=' . $clientId : ''),
                'text' => 'Активные',
                'glyphicon' => 'glyphicon-filter',
                'class' => 'btn-xs btn-' . ($state == 0 ? 'primary' : 'default'),
            ]
        ) .
            $this->render('//layouts/_buttonLink', [
                    'url' => '/sbisTenzor/document/?state=' . SBISDocumentStatus::CANCELLED . ($clientId ? '&clientId=' . $clientId : ''),
                    'text' => 'Отменённые',
                    'glyphicon' => 'glyphicon-filter',
                    'class' => 'btn-xs btn-' . ($state == SBISDocumentStatus::CANCELLED ? 'primary' : 'default'),
                ]
            ) .
            $this->render('//layouts/_buttonLink', [
                    'url' => '/sbisTenzor/document/?state=' . SBISDocumentStatus::ACCEPTED . ($clientId ? '&clientId=' . $clientId : ''),
                    'text' => 'Принятые',
                    'glyphicon' => 'glyphicon-filter',
                    'class' => 'btn-xs btn-' . ($state == SBISDocumentStatus::ACCEPTED ? 'primary' : 'default'),
                ]
            ) .
            '&nbsp;&nbsp;&nbsp;' .
            $this->render('//layouts/_buttonCreate', ['url' => '/sbisTenzor/document/add' . ($clientId ? '?clientId=' . $clientId : '')]),
    'isFilterButton' => false,
    'floatHeader' => false,
]);