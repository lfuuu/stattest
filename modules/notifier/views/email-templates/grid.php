<?php

use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\classes\grid\GridView;
use app\classes\Html;
use app\forms\user\GroupForm;

/** @var GroupForm $dataProvider */
/** @var \app\classes\BaseView $baseView */

$baseView = $this;

echo Html::formLabel('Шаблоны почтовых оповещений');
echo Breadcrumbs::widget([
    'links' => [
        'Mailer',
        ['label' => 'Шаблоны почтовых оповещений', 'url' => Url::toRoute(['/notifier/email-templates'])]
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop',
                        [
                            'url' => Url::toRoute(['/notifier/email-templates/delete', 'id' => $model->id]),
                        ]
                    );
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'attribute' => 'name',
            'label' => 'Название',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->name, ['/notifier/email-templates/edit', 'id' => $data->id]);
            },
            'width' => '*',
        ],
        [
            'attribute' => 'event',
            'label' => 'Событие',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a(
                    $data->getEvent()->event->value,
                    ['/important_events/names/edit', 'id' => $data->getEvent()->event->id],
                    ['target' => '_blank']
                );
            },
            'width' => '40%',
        ],
    ],
    'isFilterButton' => false,
    'exportWidget' => false,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/notifier/email-templates/edit']),
]);
