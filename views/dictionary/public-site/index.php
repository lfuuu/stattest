<?php

use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use kartik\grid\ActionColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\dictionary\PublicSite;

/** @var ActiveDataProvider $dataProvider */
/** @var \app\classes\BaseView $baseView */

$baseView = $this;

echo Breadcrumbs::widget([
    'links' => [
        'Справочник',
        ['label' => $this->title = 'Публичные сайты', 'url' => '/dictionary/public-site'],
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => ActionColumn::className(),
            'template' => '{update} {delete}',
            'buttons' => [
                'update' => function ($url, PublicSite $model) use ($baseView) {
                    return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]);
                },
                'delete' => function ($url, PublicSite $model) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getDeleteUrl(),
                    ]);
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'attribute' => 'title',
        ],
        [
            'attribute' => 'domain',
        ],
    ],
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/dictionary/public-site/edit']),
    'isFilterButton' => false,
    'floatHeader' => false,
]);