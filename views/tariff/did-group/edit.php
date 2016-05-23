<?php

/** @var \app\forms\tariff\DidGroupForm $model */
use app\models\DidGroup;
use yii\helpers\Url;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;


$countryList = \app\models\Country::getList();
$cityLabelList = $cityList = \app\models\City::dao()->getList(false, $model->country_id);


$title = $model->id ? 'Редактирование DID-групы №' . $model->didGroup->id : 'Новая запись';

echo Html::formLabel($title);

$links = ['Тарифы', ['label' => 'DID группы', 'url' => '/tariff/did-group']];

$cancelLink = ['/tariff/did-group'];

if ($model->id) {
    $links[] = [
        'label' => $countryList[$model->original_country_id],
        'url' => ['/tariff/did-group', 'DidGroupFilter' => ['country_id' => $model->original_country_id]]
    ];

    $links[] = [
        'label' => $cityLabelList[$model->didGroup->city_id],
        'url' => [
            '/tariff/did-group',
            'DidGroupFilter' => ['country_id' => $model->country_id, 'city_id' => $model->didGroup->city_id]
        ]
    ];

    $cancelLink = [
        '/tariff/did-group',
        'DidGroupFilter' => ['country_id' => $model->country_id, 'city_id' => $model->didGroup->city_id]
    ];
}

$links[] = [
    'label' => $title,
    'url' => ($model->id ? ['/tariff/did-group/edit', 'id' => $model->id] : ['/tariff/did-group/add'])
];

echo Breadcrumbs::widget([
    'links' => $links
]);
?>

    <div class="well">
        <?php

        $form = ActiveForm::begin([
            'id' => 'DidGroupForm',
            'type' => ActiveForm::TYPE_VERTICAL,
            'enableClientValidation' => true,
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'country_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $countryList,
                    'options' => [
                        'class' => 'formReload'
                    ]
                ],
                'city_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $cityList,
                ],
                'beauty_level' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => DidGroup::$beautyLevelNames,
                ]
            ]
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'name' => [
                    'type' => Form::INPUT_TEXT
                ]
            ]
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 2,
            'attributes' => [
                'id' => [
                    'type' => Form::INPUT_RAW,
                    'value' => Html::activeHiddenInput($model, 'id')
                ],

                'actions' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        Html::tag(
                            'div',
                            Html::button('Отменить', [
                                'class' => 'btn btn-link',
                                'style' => 'margin-right: 15px;',
                                'onClick' => 'self.location = "' . Url::toRoute($cancelLink) . '";',
                            ]) .
                            Html::submitButton('Сохранить',
                                [
                                    'class' => 'btn btn-primary',
                                    'name' => 'save',
                                    'value' => 'Сохранить'
                                ]),
                            ['style' => 'text-align: right; padding-right: 0px;']
                        )
                ],
            ],
        ]);

        ActiveForm::end();
        ?>
    </div>
    <script>
        jQuery(document).ready(function () {
            $('.formReload').on('change', function () {
                document.getElementById('<?= $form->getId()?>').submit();
            });
        });
    </script>
