<?php

use app\classes\BaseView;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use app\classes\grid\GridView;
use app\classes\Html;
use yii\widgets\Breadcrumbs;

/** @var BaseView $baseView */
/** @var $dataProvider ActiveDataProvider */
/** @var \app\modules\uu\forms\TagsForm $form */

$baseView = $this;
echo Html::formLabel('Метки');
echo Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => 'Метки тарифов', 'url' => Url::toRoute(['/uu/tags/'])],
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'actions' => [
            'class' => 'kartik\grid\ActionColumn',
            'template' => Html::tag('div', '{update}', ['class' => 'text-center']),
            'buttons' => [
                'update' => function ($url, $model) use ($baseView) {
                    return $baseView->render('//layouts/_actionEdit', [
                        'url' => Url::toRoute([
                            '/uu/tags/edit',
                            'id' => $model->id,
                        ]),
                    ]);
                },
            ],
            'hAlign' => 'left',
        ],
        [
            'attribute' => 'id',
            'width' => '5%',
        ],
        [
            'attribute' => 'name',
            'width' => '*',
        ],
        [
            'label' => 'Используется',
            'format' => 'raw',
            'value' => function ($model) use ($form) {

                $result = [];

                foreach($model->getTariffTags()->with(['tariff', 'tariff.serviceType'])->all() as $tariffTag) {
                    /** @var \app\modules\uu\models\Tariff $tariff */
                    $tariff = $tariffTag->tariff;
                    $result[] = Html::tag(
                        'div',
                        Html::a($tariff->serviceType->name.': ' .Html::tag('span', $tariff->name, [
                            'style' => ['color' => '#fff', 'font-size' => '8pt'],
                                ]), $tariff->getUrl(),['target' => '_blank']),
                        ['class' => 'label label-info']
                    );
                }
            return implode('&nbsp;', $result);



            },
            'width' => '30%',
        ],
    ],
    'floatHeader' => false,
    'isFilterButton' => false,
    'exportWidget' => false,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/uu/tags/edit?id=0']),
    'toggleData' => false,
]);