<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use yii\helpers\ArrayHelper;
use app\helpers\MediaFileHelper;
use app\classes\Html;
use yii\widgets\Breadcrumbs;
use yii\helpers\Url;
use app\forms\organization\OrganizationForm;
use app\models\Country;
use app\models\Person;

/** @var $model OrganizationForm */

if (!empty($title)) {
    echo Html::formLabel($title);
    echo Breadcrumbs::widget([
        'links' => [
            ['label' => 'Организации', 'url' => Url::toRoute(['/organization'])],
            $title
        ],
    ]);
}
?>

<link href="/css/behaviors/autocomplete-loading.css" type="text/css" rel="stylesheet" />
<link href="/css/behaviors/image-preview-select.css" type="text/css" rel="stylesheet" />

<script type="text/javascript" src="/js/behaviors/find-bik.js"></script>
<script type="text/javascript" src="/js/behaviors/organization.js"></script>
<script type="text/javascript" src="/js/behaviors/image-preview-select.js"></script>

<div class="container<?= (!empty($title) ? ' well' : '')?>" style="width: 100%; padding-top: 20px;">
    <?php
    $form_options = [
        'type' => ActiveForm::TYPE_VERTICAL,
        'id' => 'OrganizationFrm',
    ];
    if ($mode == 'duplicate')
        $form_options['action'] = '/organization/add';
    $form = ActiveForm::begin($form_options);
    ?>

    <fieldset style="width: 100%;">
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'firma')
                        ->textInput(['readonly' => $mode == 'duplicate' ? true : false])
                        ->label('Код организации ("mcn", "ooomcn" etc)');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'country_id')
                        ->dropDownList(
                            ArrayHelper::map(
                                Country::find()->where(['in_use' => 1])->orderBy('code desc')->all(),
                                'code',
                                'name'
                            ),
                            [
                                'prompt' => 'Выберите страну',
                                'id' => 'Country',
                                'readonly' => $mode == 'duplicate' ? true : false,
                            ]
                        )
                        ->label('Страна');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'lang_code')
                        ->dropDownList(
                            ArrayHelper::map(
                                array_map(
                                    function($item){
                                        $item['lang'] = explode('-', $item['lang'])[0];
                                        return $item;
                                    },
                                    Country::find()->select('lang')->distinct()->where(['in_use' => 1])->orderBy('lang desc')->all()
                                ),
                                'lang',
                                'lang'
                            ),
                            [
                                'readonly' => $mode == 'duplicate' ? true : false
                            ]
                        )
                        ->label('Язык');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'actual_from')
                        ->widget(DateControl::classname(), [
                            'type' => DateControl::FORMAT_DATE,
                            'ajaxConversion' => false,
                            'disabled' => $mode == 'edit',
                            'options' => [
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'orientation' => 'top left',
                                    'startDate' =>  'today',
                                ]
                            ]
                        ])
                        ->label('Дата активации');
                    ?>
                </div>
            </div>
        </div>

        <div style="height: 25px;">&nbsp;</div>
    </fieldset>

    <fieldset style="width: 100%;">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'name')->label('Краткое название');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'legal_address')->label('Юридический адрес');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'full_name')->label('Полное название');
                    ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'post_address')->label('Почтовый адрес');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'director_id')
                        ->dropDownList(
                            ArrayHelper::map(Person::find()->all(), 'id', 'name_nominative'),[
                                'prompt' => 'Выберите директора',
                                'style' => 'width: 80%;',
                            ]
                        )
                        ->label('Директор');
                    ?>
                    <a href="/person/add" target="_blank" class="btn btn-success" style="float: right; margin-top: -50px; width: 120px;">
                        <i class="glyphicon glyphicon-plus"></i>
                        Добавить
                    </a>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'accountant_id')
                        ->dropDownList(
                            ArrayHelper::map(Person::find()->all(), 'id', 'name_nominative'), [
                                'prompt' => 'Выберите бухгалтера',
                                'style' => 'width: 80%;',
                            ]
                        )
                        ->label('Главный бухгалтер');
                    ?>
                    <a href="/person/add" target="_blank" class="btn btn-success" style="float: right; margin-top: -50px; width: 120px;">
                        <i class="glyphicon glyphicon-plus"></i>
                        Добавить
                    </a>
                </div>
            </div>
        </div>

        <div style="height: 25px;">&nbsp;</div>
    </fieldset>

    <fieldset style="width: 50%; padding-right: 15px; float: left;">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'vat_rate')
                        ->textInput([
                            'id' => 'VatRate',
                        ])
                        ->label('Ставка НДС');

                    echo $form->field($model, 'is_simple_tax_system')
                        ->checkbox([
                            'id' => 'IsSimpleTaxSystem',
                            'label' => Html::tag('span', 'Упрощенная система налогообложения', [
                                'style' => 'display: inline-block; margin-top: 2px;'
                            ]),
                        ], true);
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'tax_registration_id')->label('ИНН');
                    ?>
                </div>

                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'tax_registration_reason')->label('КПП');
                    ?>
                </div>

                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'registration_id')->label('ОГРН');
                    ?>
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset style="width: 50%; padding-left: 15px;">
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_account')->label('Расчетный счет');
                    ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_bik')->textInput([
                        'class' => 'search-bik',
                    ])->label('БИК');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_swift')->label('SWIFT');
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_name')->label('Название банка');
                    ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'bank_correspondent_account')->label('Кор. счет');
                    ?>
                </div>
            </div>
        </div>
    </fieldset>

    <div style="height: 25px;">&nbsp;</div>

    <fieldset style="width: 50%; padding-right: 15px; float: left;">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'contact_phone')->label('Телефон');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'contact_fax')->label('Факс');
                    ?>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'contact_email')->input('email')->label('E-mail');
                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'contact_site')->label('Сайт URL');
                    ?>
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset style="width: 50%; padding-left: 15px; ">
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'logo_file_name')
                        ->dropDownList(
                            MediaFileHelper::findByPattern('ORGANIZATION_LOGO_DIR', '*.{gif,png,jpg,jpeg}', 'assoc'),
                            [
                                'prompt' => 'Выбрать логотип',
                                'data-source' => Yii::$app->params['ORGANIZATION_LOGO_DIR'],
                                'data-target' => '#full_frm_logo_file_name',
                                'class' => 'image_preview_select',
                            ]
                        )
                        ->label('Логотип компании');
                    ?>
                    <div id="full_frm_logo_file_name" class="image_preview"></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?php
                    echo $form->field($model, 'stamp_file_name')
                        ->dropDownList(
                            MediaFileHelper::findByPattern('STAMP_DIR', '*.{gif,png,jpg,jpeg}', 'assoc'),
                            [
                                'prompt' => 'Выбрать печать',
                                'data-source' => Yii::$app->params['STAMP_DIR'],
                                'data-target' => '#full_frm_stamp_file_name',
                                'class' => 'image_preview_select',
                            ]
                        )
                        ->label('Печать компании');
                    ?>
                    <div id="full_frm_stamp_file_name" class="image_preview"></div>
                </div>
            </div>
        </div>
    </fieldset>

    <div class="row">
        <div class="col-sm-12" style="padding-top: 30px; padding-left: 30px;">
            <?php
            echo Form::widget([
                'model' => $model,
                'form' => $form,
                'attributes' => [
                    'id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'id')],
                    'organization_id' => ['type' => Form::INPUT_RAW, 'value' => Html::activeHiddenInput($model, 'organization_id')],
                    'actions' => [
                        'type' => Form::INPUT_RAW,
                        'value' =>
                            Html::tag(
                                'div',
                                Html::button('Отменить', [
                                    'class' => 'btn btn-link',
                                    'style' => 'margin-right: 15px;',
                                    'onClick' => 'self.location = "' . Url::toRoute(['/organization']) . '";',
                                ]) .
                                Html::submitButton('Сохранить', ['class' => 'btn btn-primary']),
                                ['style' => 'text-align: right; padding-right: 0px;']
                            )
                    ],
                ],
            ]);
            ?>
        </div>
    </div>

    <?php
    ActiveForm::end();
    ?>
</div>