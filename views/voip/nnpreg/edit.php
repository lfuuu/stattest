<?php

/**
 * @var \app\forms\voip\NnpregForm $model
 * @var bool $creatingMode
 * @var array $checkList
 * @var array $statusInfo
 */

use app\classes\enum\VoipRegistrySourceEnum;
use app\classes\Html;
use app\models\billing\Trunk;
use app\models\City;
use app\models\Country;
use app\models\Number;
use app\models\voip\Registry;
use app\modules\nnp\models\NdcType;
use kartik\builder\Form;
use kartik\widgets\ActiveForm;
use kartik\widgets\DatePicker;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;


$numbersWithoutRegistry = $checkList
    ? array_column(array_filter($checkList, function ($item) {
        return $item['filling'] == 'fill' && !$item['registry_id'];
    }), 'end', 'start')
    : [];

$countryList = \app\modules\nnp\models\Country::getList();


$title = $model->id ? 'Редактирование записи №' . $model->registry->id . ' (' . $model->registry->number_from . ' - ' . $model->registry->number_to . ')' : 'Новая запись';

echo Html::formLabel($title);

$links = ['Телефония', ['label' => 'Реестр номеров 2.0 (ННП)', 'url' => '/voip/nnpreg']];

$links[] = [
    'label' => $title,
    'url' => ['/voip/nnpreg'],
];

echo Breadcrumbs::widget([
    'links' => $links
]);

?>

    <div class="well">
        <?php

        $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_VERTICAL,
            'enableClientValidation' => true,
        ]);

        $this->registerJsVariable('registryFormId', $form->getId());

        // строка 1
        $line1Attributes = [];

        $line1Attributes['country_id'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => $countryList,
            'options' => [
                'class' => 'formReload select2'
            ]
        ];

        $line1Attributes['ndc_type_id'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => NdcType::getList(),
            'options' => [
                'class' => 'formReload select2',
            ],
        ];

        $line1Attributes['operator_id'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => \app\modules\nnp\models\Operator::getList(true, false, $model->country_id),
            'options' => [
                'class' => 'formReload select2',
            ],
        ];


        $line1Attributes['source'] = [
            'type' => Form::INPUT_DROPDOWN_LIST,
            'items' => \app\models\voip\Source::getList(),
            'options' => [
                'class' => 'formReload select2',
            ],

        ];

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => count($line1Attributes),
            'attributes' => $line1Attributes
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'check-number' => [
                    'type' => Form::INPUT_RAW,
                    'value' => $value,
                ],
                'id' => [
                    'type' => Form::INPUT_RAW,
                    'value' => Html::activeHiddenInput($model, 'id')
                ],

            ],
        ]);

        ActiveForm::end();
        ?>
    </div>
<?php

echo \app\classes\grid\GridView::widget([
    'dataProvider' => $model->search(),
    'filterModel' => $model,
    'columns' => [
        [
            'attribute' => 'region_source',
        ],
        [
            'attribute' => 'city_source',
        ],
        [
            'attribute' => 'full_number_from',
            'value' => function (\app\modules\nnp\models\NumberRange $row) {
                return \app\modules\nnp\models\NumberRange::formatNumber($row, true);
            },
        ],
        [
            'attribute' => 'full_number_to',
            'value' => function (\app\modules\nnp\models\NumberRange $row) {
                return \app\modules\nnp\models\NumberRange::formatNumber($row, false);
            },
        ],
        [
            'label' => 'Действие',
            'format' => 'raw',
            'value' => function (\app\modules\nnp\models\NumberRange $row) use ($model) {
                    static $c = [];

                    if (!array_key_exists($c, $row->city_id)) {
                        $c[$row->city_id] = $row->city_id ? City::find()->where(['id' => $row->city_id])->exists() : false;
                    }

                return $c[$row->city_id] || !NdcType::isCityDependent($row->ndc_type_id) ? Html::a('>>>', Url::to(['/voip/registry/add', 'RegistryForm' => [
                    'country_id' => $row->country_code,
                    'city_id' => $row->city_id,
                    'source' => $model->source,
                    'ndc_type_id' => $row->ndc_type_id,
                    'number_from' => (string)($row->number_from == 0 ? str_repeat('0', strlen($row->number_to)) : $row->number_from),
                    'number_to' => (string)$row->number_to,
                    'ndc' => (string)$row->ndc_str,
                    'id' => 0,
                    'comment' => 'From nnp number range #' . $row->id,
                    'nnp_operator_id' => $row->operator_id,
                ]])) : '';
            },
        ]
    ],
    'floatHeader' => false,
    'isFilterButton' => false,
    'exportWidget' => false,
    'toggleData' => false,
]);
?>
    <script>
        +function ($, formId) {
            'use strict';

            $(function () {
                $('.formReload').on('change', function () {
                    document.getElementById(formId).submit();
                });
            });
        }(
            jQuery,
            window.frontendVariables.voipNnpregEdit.registryFormId
        );
    </script>
