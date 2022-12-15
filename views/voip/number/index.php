<?php
/**
 * Список номеров
 */

use app\classes\grid\column\universal\BeautyLevelColumn;
use app\classes\grid\column\universal\CityColumn;
use app\classes\grid\column\universal\CountryColumn;
use app\classes\grid\column\universal\DidGroupColumn;
use app\classes\grid\column\universal\IntegerColumn;
use app\classes\grid\column\universal\IntegerRangeColumn;
use app\classes\grid\column\universal\IsNullAndNotNullColumn;
use app\classes\grid\column\universal\NumberStatusColumn;
use app\classes\grid\column\universal\RegionColumn;
use app\classes\grid\column\universal\SourceColumn;
use app\classes\grid\column\universal\StringColumn;
use app\classes\grid\GridView;
use app\classes\Html;
use app\models\DidGroup;
use app\modules\nnp\models\Operator;
use app\models\filter\voip\NumberFilter;
use app\models\Number;
use app\models\voip\Registry;
use app\modules\nnp\column\NdcTypeColumn;
use app\modules\nnp\column\OperatorColumn;
use app\modules\sim\columns\ImsiPartnerColumn;
use kartik\grid\ActionColumn;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/**
 * @var app\classes\BaseView $this
 * @var NumberFilter $filterModel
 * @var int $currentClientAccountId
 */

?>

<?= app\classes\Html::formLabel($this->title = 'Номера') ?>
<?= Breadcrumbs::widget([
    'links' => [
        'Телефония',
        ['label' => $this->title, 'url' => '/voip/number/'],
    ],
]) ?>

    <!-- div class="well">
        <?= '' /*$this->render('_indexGroupEdit', [
            'city_id' => $filterModel->city_id,
            'currentClientAccountId' => $currentClientAccountId,
        ]) */ ?>
    </div -->

<?php
$dataProvider = $filterModel->search();

if (
    Yii::$app->user->can('voip.change-number-status')
    || \Yii::$app->user->can('voip.delete-number')
) {
    $numbers = [];
    $query = clone $dataProvider->query;
    $isMoreNumbers = $query->count() > 10000;

    if (!$isMoreNumbers) {
        $numbers = $query->select('number')->column();

        echo "<div>";
        echo "<div class=well style='width: 400px; float: left;'>";
        echo Html::tag('b', 'Изменить статус на') . '<br>';
        echo Html::beginForm(Url::to(['change-status']));
        echo Html::hiddenInput('numbers', json_encode($numbers));
        if (Yii::$app->user->can('voip.change-number-status')) {
            echo Html::button('Применить', ['class' => 'btn btn-primary', 'type' => 'submit', 'style' => 'margin-left: 10px', 'name' => 'set-status']);
            echo Html::dropDownList('status', null,
                DidGroup::getEmptyList(true) +
                [
                    Number::STATUS_NOTSALE => 'Не продается',
                    Number::STATUS_INSTOCK => 'Свободен',
                    Number::STATUS_RELEASED => 'Откреплен',
                ], ['class' => 'form-control pull-left', 'style' => 'width: 250px']);
            echo "</div>";

            echo "<div class=well style='width: 400px; float: left; margin-left: 10px;'>";
            echo Html::tag('b', 'Изменить степень красивости') . '<br>';
            echo Html::button('Применить', ['class' => 'btn btn-primary', 'type' => 'submit', 'style' => 'margin-left: 10px', 'name' => 'set-beauty-level']);
            echo Html::dropDownList('beauty-level', null,
                DidGroup::getEmptyList(true) + DidGroup::$beautyLevelNames + ['original' => 'Изначальный'], ['class' => 'form-control pull-left', 'style' => 'width: 250px']);
            echo "</div>";

            echo "<div class=well style='width: 400px; float: left; margin-left: 10px;'>";
            echo Html::tag('b', 'Изменить DID-группу') . '<br>';
            if (!$filterModel->ndc_type_id) {
                echo Html::tag('span', 'Для получения списка DID-групп необходимов выбрать "Страну" и "Тип номера"', ['class' => 'text-info']);
            } else {
                echo Html::button('Применить', ['class' => 'btn btn-primary', 'type' => 'submit', 'style' => 'margin-left: 10px', 'name' => 'set-did-group']);
                echo Html::dropDownList('did_group_id', null,
                    DidGroup::getList($isWithEmpty = true, $filterModel->country_id, $filterModel->city_id, $filterModel->ndc_type_id), ['class' => 'form-control pull-left', 'style' => 'width: 250px']);
            }
        }
        echo "</div>";

        if (\Yii::$app->user->can('voip.delete-number')) {
            echo "<div class=well style='width: 150px; float: left; margin-left: 10px;'>";
            echo Html::button(' Удалить', [
                'class' => 'btn btn-danger glyphicon glyphicon-trash',
                'style' => 'margin-left: 10px',
                'type' => 'submit',
                'name' => 'delete-numbers',
            ]);
            echo "</div>";
        }


        echo Html::endForm();
        echo "</div>";

        echo "<div style='clear: both;'></div>";
    } else {
        echo Html::tag('small', 'Слишком много номеров для измнения статуса (>10000)', ['class' => 'text-muted']);
    }
}

?>

<?php
$baseView = $this;
$month0 = (new DateTimeImmutable())->modify('first day of this month');
$month1 = $month0->modify('-1 month');
$month2 = $month1->modify('-1 month');

$columns = [
    [
        'class' => ActionColumn::class,
        'template' => '{update}', // {delete}
        'buttons' => [
            'update' => function ($url, Number $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionEdit', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
            'delete' => function ($url, Number $model, $key) use ($baseView) {
                return $baseView->render('//layouts/_actionDrop', [
                        'url' => $model->getUrl(),
                    ]
                );
            },
        ],
        'hAlign' => GridView::ALIGN_CENTER,
    ],
    [
        'attribute' => 'number',
        'class' => IntegerRangeColumn::class,
        'options' => ['style' => 'width: 150px;'],
    ],
    [
        'attribute' => 'beauty_level',
        'class' => BeautyLevelColumn::class,
    ],
    [
        'attribute' => 'original_beauty_level',
        'class' => BeautyLevelColumn::class,
    ],
    [
        'label' => 'Страна',
        'attribute' => 'country_id',
        'format' => 'html',
        'class' => CountryColumn::class,
        'value' => function (Number $number) {
            return $number->country_code ?
                $number->country->getLink()
                : Yii::t('common', '(not set)');
        },
    ],
    [
        'attribute' => 'city_id',
        'class' => CityColumn::class,
    ],
    [
        'attribute' => 'region',
        'class' => RegionColumn::class,
    ],
    [
        'attribute' => 'status',
        'class' => NumberStatusColumn::class,
    ],
    [
        'attribute' => 'ndc_type_id',
        'class' => NdcTypeColumn::class
    ],
    [
        'attribute' => 'did_group_id',
        'class' => DidGroupColumn::class,
        'country_id' => $filterModel->country_id,
        'city_id' => $filterModel->city_id,
        'ndc_type_id' => $filterModel->ndc_type_id,
        'value' => function (Number $number) {
            $didGroup = $number->didGroup;
            
            return $didGroup ? Html::a($didGroup->name, $didGroup->getUrl()) : Yii::t('common', '(not set)');
        },
    ],
    [
        'attribute' => 'client_id',
        'class' => IntegerColumn::class,
        'isNullAndNotNull' => true,
        'format' => 'html',
        'value' => function (Number $number) {
            return $number->client_id ?
                ($number->clientAccount ? $number->clientAccount->getLink() : $number->client_id) :
                Yii::t('common', '(not set)');
        },
    ],
    [
        'label' => 'Звонков за ' . Yii::$app->formatter->asDate($month2, 'php:m'),
        'attribute' => 'calls_per_month_2',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'label' => 'Звонков за ' . Yii::$app->formatter->asDate($month1, 'php:m'),
        'attribute' => 'calls_per_month_1',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'label' => 'Звонков за ' . Yii::$app->formatter->asDate($month0, 'php:m'),
        'attribute' => 'calls_per_month_0',
        'class' => IntegerRangeColumn::class,
    ],
    [
        'attribute' => 'usage_id',
        'class' => IsNullAndNotNullColumn::class,
        'format' => 'html',
        'value' => function (Number $number) {
            return $number->usage_id ?
                $number->usage ?
                    Html::a(
                        Html::encode($number->usage_id),
                        $number->usage->getUrl()
                    ) : $number->usage_id
                :
                Yii::t('common', '(not set)');
        },
    ],
    [
        'label' => 'Реестр',
        'attribute' => 'registry_id',
        'value' => function ($model) {
            if ($model->registry_id) {
                return Html::a('Реестр №' . $model->registry_id, ['voip/registry/edit', 'id' => $model->registry_id]);
            }
            return '';
        },
        'filter' => Select2::widget([
            'model' => $filterModel,
            'data' => Registry::getList(),
            'attribute' => 'registry_id'
        ]),
        'format' => 'raw',
    ],
    [
        'attribute' => 'solution_number',
        'value' => function ($model) {
            if ($model->registry) {
                $solutionNumber = $model->registry->solution_number;
                return Html::a($solutionNumber, ['voip/registry', 'RegistryFilter[solution_number]' => $solutionNumber]);
            }
            return '';
        },
        'filter' => Select2::widget([
            'model' => $filterModel,
            'data' => Registry::find()->select('solution_number')->indexBy('solution_number')->column(),
            'attribute' => 'solution_number',
            'value' => $filterModel->solution_number,
        ]),
        'format' => 'raw',
        'label' => 'Номер решения'
    ],
    [
        'attribute' => 'solution_date',
        'value' => function ($model) {
            if ($model->registry) {
                return $model->registry->solution_date;
            }
            return '';
        },
        'filter' => DatePicker::widget([
            'model' => $filterModel,
            'attribute' => 'solution_date',
            'value' => $filterModel->solution_date
        ]),
        'label' => 'Дата решения'
    ],
    [
        'attribute' => 'registry_number_from',
        'value' => function ($model) {
            if ($model->registry) {
                $numberFullFrom = $model->registry->number_full_from;
                return Html::a($numberFullFrom, ['voip/number', 'NumberFilter[number]' => $numberFullFrom]);
            }
            return '';
        },
        'class' => StringColumn::class,
        'format' => 'raw',
        'label' => 'Начальный номер'
    ],
    [
        'attribute' => 'numbers_count',
        'value' => function (Number $model) {
            /** @var Number $model */
            if ($model->registry) {
                return $model->registry->numbers_count;
            }
            return '';
        },
        'label' => 'Количество номеров'
    ],
    [
        'attribute' => 'source',
        'class' => SourceColumn::class,
    ],
    [
        'label' => 'MVNO-партнер',
        'attribute' => 'mvno_partner_id',
        'class' => ImsiPartnerColumn::class,
        'isWithNullAndNotNull' => true,
    ],
    [
        'attribute' => 'nnp_operator_id',
        'filter' => Select2::widget([
            'model' => $filterModel,
            'data' => Operator::getList(true, false, $filterModel->country_id),
            'attribute' => 'nnp_operator_id',
        ]),
        'value' => function (Number $model) {
            /** @var Number $model */
            if ($model->nnpOperator) {
                return $model->nnpOperator->name;
            }
            return '(не задано)';
        },
        
    ],
    [
        'attribute' => 'imsi',
        'class' => IsNullAndNotNullColumn::class,
    ],
    [
        'attribute' => 'iccid',
        'format' => 'raw',
        'value' => function (Number $number) {
            return $number->imsi && $number->imsiModel ? $number->imsiModel->getLink() : '';
        },
    ],
    [
        'attribute' => 'number_tech',
        'class' => StringColumn::class,
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $filterModel,
    'extraButtons' => $this->render('//layouts/_buttonCreate', ['url' => '/voip/registry/add/']),
    'columns' => $columns,
]);
