<?php

use kartik\widgets\ActiveForm;
use app\classes\grid\GridView;
use yii\helpers\Html;
use kartik\builder\Form;

/** @var \app\models\filter\voip\MonitorFilter $searchModel */


$urlData = [
    '/voip/monitor',
    'MonitorFilter' => [
        'range' => $searchModel->range,
        'number_a' => $searchModel->number_a,
        'number_b' => $searchModel->number_b,
    ]];


$breadCrumbLinks = [
    'Телефония',
    ['label' => 'Мониторинг', 'url' => $urlData],
];

?>

<?= \yii\widgets\Breadcrumbs::widget(['links' => $breadCrumbLinks]) ?>

    <form>

        <div class="well">
            <?php

            $form = ActiveForm::begin([
                'type' => ActiveForm::TYPE_VERTICAL,
                'enableClientValidation' => true,
            ]);

            $this->registerJsVariable('registryFormId', $form->getId());

            // строка 1
            $line1Attributes = [];

            $line1Attributes['range'] = [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $searchModel->getRanges(),
            ];

            $line1Attributes['number_a'] = [
                'type' => Form::INPUT_TEXT,
            ];

            $line1Attributes['number_b'] = [
                'type' => Form::INPUT_TEXT,
            ];

            $line1Attributes['is_with_session_time'] = [
                'type' => Form::INPUT_CHECKBOX,
            ];


            echo Form::widget([
                'model' => $searchModel,
                'form' => $form,
                'columns' => count($line1Attributes),
                'attributes' => $line1Attributes
            ]);

            echo Html::submitButton('Поиск', [
                'class' => 'btn btn-info',
                'name' => 'check-numbers',
                'value' => 'Поиск',
                'style' => 'width: 300px',
            ]);


            ActiveForm::end();
            ?>
        </div>
    </form>

<?php

$columns = [[
    'label' => 'Сервер',
    'value' => function ($row) {
        static $c = [];
        if (!$c) {
            $c = \app\models\Region::getList(false, \app\models\Country::RUSSIA);
        }

        return $c[$row->server_id] ?? '';
    }
], 'src_number', 'dst_number', 'connect_time', [
    'label' => 'Ожидание соединения',
    'value' => function ($row) {
        return (new DateTimeImmutable($row->connect_time))->diff(new DateTimeImmutable($row->setup_time))->s;
    }
], 'session_time', 'src_route', 'dst_route', [
    'format' => 'raw',
    'value' => function ($row) {
        return $row->server_id == 99 ? \app\classes\Html::tag('audio', '', [
            'preload' => 'none',
            'id' => 'audio' . $row->id,
            'controls' => '1',
            'src' => \yii\helpers\Url::to(['/voip/monitor/load', 'key' => \app\classes\Encrypt::encodeArray(['server_id' => $row->server_id, 'id' => $row->id])])
        ]) : '';
    }
]];

echo GridView::widget([
    'dataProvider' => $searchModel->search(),
    'filterModel' => $searchModel,
    'columns' => $columns,
    'isFilterButton' => false,
]);


