<?php

use app\classes\grid\GridView;
use app\classes\Html;
use app\forms\user\GroupForm;
use app\models\important_events\ImportantEventsRulesConditions;
use kartik\grid\ActionColumn;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var GroupForm $dataProvider */
/** @var \yii\web\View $baseView */

$baseView = $this;

$recordBtns = [
    'delete' => function ($url, $model, $key) {
        return Html::a(
            '<span class="glyphicon glyphicon-trash"></span> Удаление',
            ['/important_events/rules/delete', 'rule_id' => $model->id],
            [
                'title' => Yii::t('kvgrid', 'Delete'),
                'data-pjax' => 0,
                'onClick' => 'return confirm("Вы уверены, что хотите удалить правило ?")',
            ]
        );
    },
];

echo Html::formLabel('Список правил на события');
echo Breadcrumbs::widget([
    'links' => [
        ['label' => 'Значимые события', 'url' => Url::toRoute(['/important_events/report'])],
        'Список правил на события'
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => ActionColumn::className(),
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, ImportantEventsRulesConditions $model, $key) use ($baseView) {
                    return $baseView->render('//layouts/_actionDrop', [
                            'url' => Url::toRoute(['/important_events/rules/delete', 'id' => $model->id]),
                        ]
                    );
                },
            ],
            'hAlign' => GridView::ALIGN_CENTER,
        ],
        [
            'attribute' => 'title',
            'label' => 'Название',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->title, ['/important_events/rules/edit', 'id' => $data->id]);
            },
            'width' => '*',
        ],
        [
            'label' => 'Действие',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->getAction($data->action)->title;
            },
            'width' => '20%',
        ],
        [
            'label' => 'Событие',
            'format' => 'raw',
            'value' => function ($data) {
                return $data->eventInfo->value . ' (' . $data->event . ')';
            },
            'width' => '20%',
        ],
        [
            'label' => 'Шаблон сообщения',
            'format' => 'raw',
            'value' => function ($data) {
                return Html::a($data->template->name, ['/message/template/edit', 'id' => $data->template->id], ['target' => '_blank']);
            },
            'width' => '20%',
        ],
        [
            'label' => 'Условие',
            'format' => 'raw',
            'value' => function ($data) {
                $result = [];

                foreach ($data->allConditions as $condition) {
                    $result[] = Html::tag('b', $condition->property) . ' ' . ImportantEventsRulesConditions::$conditions[$condition->condition] . ' ' . Html::tag('b', $condition->value);
                }

                return implode('<br />', $result);
            },
            'width' => '20%',
        ],
    ],
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/important_events/rules/edit']),
]);