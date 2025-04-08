<?php

use app\assets\AppAsset;
use app\classes\Html;
use app\forms\organization\OrganizationForm;
use app\helpers\MediaFileHelper;
use app\models\Country;
use app\models\Language;
use app\models\Person;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use kartik\tabs\TabsX;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var OrganizationForm $model */
/** @var \app\models\Organization $history */
/** @var string $mode */

$this->registerCssFile('@web/css/behaviors/autocomplete-loading.css', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/behaviors/image-preview-select.css', ['depends' => [AppAsset::class]]);

$this->registerJsFile('@web/js/behaviors/find-bik.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/behaviors/organization.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/behaviors/image-preview-select.js', ['depends' => [AppAsset::class]]);

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

<div class="container<?= (!empty($title) ? ' well' : '') ?>" style="width: 100%; padding-top: 20px;">
    <?php
    $formOptions = [
        'type' => ActiveForm::TYPE_VERTICAL,
        'id' => 'OrganizationFrm',
    ];
    if ($mode === 'duplicate') {
        $formOptions['action'] = '/organization/add';
    }
    $form = ActiveForm::begin($formOptions);

    $languagesTabs = [];
    foreach (Language::getList() as $languageCode => $languageTitle) {
        $language =
            !is_file(__DIR__ . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . $languageCode . '.php')
                ? Language::LANGUAGE_DEFAULT
                : $languageCode;

        $languagesTabs[] = [
            'label' =>
                Html::tag(
                    'div', '',
                    ['title' => $languageTitle, 'class' => 'flag flag-' . explode('-', $languageCode)[0]]
                ) . $languageTitle,
            'content' => $this->render('i18n/' . $language, [
                'form' => $form,
                'organization' => $history,
                'lang' => $languageCode,
            ]),
            'headerOptions' => [],
            'options' => ['style' => 'white-space: nowrap;'],
        ];
    }
    ?>

    <fieldset style="width: 100%;">
        <div class="row">
            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?= $form->field($model, 'firma')
                        ->textInput([
                            'readonly' => $mode === 'duplicate',
                        ])
                        ->label('Код организации ("mcn", "ooomcn" etc)');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?= $form->field($model, 'country_id')
                        ->dropDownList(
                            Country::getList(),
                            [
                                'prompt' => 'Выберите страну',
                                'id' => 'Country',
                                'readonly' => $mode === 'duplicate',
                            ]
                        )
                        ->label('Страна');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?= $form->field($model, 'lang_code')
                        ->dropDownList(
                            Country::getListTrait(
                                $isWithEmpty = false,
                                $isWithNullAndNotNull = false,
                                $indexBy = 'lang',
                                $select = new \yii\db\Expression('DISTINCT lang'),
                                $orderBy = ['lang' => SORT_DESC],
                                $where = ['in_use' => 1]
                            ),
                            [
                                'readonly' => $mode === 'duplicate',
                            ]
                        )
                        ->label('Язык');
                    ?>
                </div>
            </div>

            <div class="col-sm-3">
                <div class="col-sm-12">
                    <?= $form->field($model, 'actual_from')
                        ->widget(DateControl::classname(), [
                            'type' => DateControl::FORMAT_DATE,
                            'ajaxConversion' => false,
                            'disabled' => $mode === 'edit',
                            'options' => [
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'orientation' => 'bottom left',
                                    'startDate' => 'today',
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
        <?php
        echo TabsX::widget([
            'id' => 'tabs-organization-lang',
            'items' => $languagesTabs,
            'containerOptions' => [
                'class' => 'col-sm-12 localization-tabs',
            ],
            'position' => TabsX::POS_ABOVE,
            'bordered' => false,
            'encodeLabels' => false,
        ]);
        ?>

        <div class="row">
            <div class="col-sm-6" style="padding-left: 30px;">
                <?= $form
                    ->field($model, 'director_id')
                    ->dropDownList(
                        Person::find()->indexBy('id')->all(), [
                            'prompt' => 'Выберите директора',
                            'style' => 'width: 75%;',
                        ]
                    )
                    ->label('Директор');
                ?>
                <a href="/person/add" target="_blank" class="btn btn-success" style="float: right; margin-top: -50px; width: 100px;">
                    <i class="glyphicon glyphicon-plus"></i>
                    Добавить
                </a>
            </div>
            <div class="col-sm-6" style="padding-left: 30px;">
                <?= $form
                    ->field($model, 'accountant_id')
                    ->dropDownList(
                        Person::find()->indexBy('id')->all(), [
                            'prompt' => 'Выберите бухгалтера',
                            'style' => 'width: 75%',
                        ]
                    )
                    ->label('Главный бухгалтер');
                ?>
                <a href="/person/add" target="_blank" class="btn btn-success" style="float: right; margin-top: -50px; width: 100px;">
                    <i class="glyphicon glyphicon-plus"></i>
                    Добавить
                </a>
            </div>
        </div>
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

                    echo $form->field($model, 'invoice_counter_range_id')
                        ->dropDownList(\app\models\Organization::$invoiceCounterRangeNames)
                    ;

                    echo $form->field($model, 'is_agent_tax_rate')
                        ->checkbox([
                            'id' => 'IsAgentTaxRate',
                            'label' => Html::tag('span', 'Агентская схема НДС', [
                                'style' => 'display: inline-block; margin-top: 2px;'
                            ]),
                        ], true);

                    ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?= $form->field($model, 'tax_registration_id')->label('ИНН') ?>
                </div>

                <div class="col-sm-12">
                    <?= $form->field($model, 'tax_registration_reason')->label('КПП') ?>
                </div>

                <div class="col-sm-12">
                    <?= $form->field($model, 'registration_id')->label('ОГРН') ?>
                </div>
            </div>

        </div>
    </fieldset>

    <fieldset style="width: 50%; padding-left: 15px;">
        <?php
        echo TabsX::widget([
            'id' => 'tabs-organization-settlement-account',
            'items' => [
                [
                    'label' => 'Рассчетный счет',
                    'content' => $this->render('settlement-account/russia', [
                        'form' => $form,
                        'organization' => $history,
                    ]),
                ],
                [
                    'label' => 'IBAN',
                    'content' => $this->render('settlement-account/iban', [
                        'form' => $form,
                        'organization' => $history,
                    ]),
                ],
                [
                    'label' => 'SWIFT',
                    'content' => $this->render('settlement-account/swift', [
                        'form' => $form,
                        'organization' => $history,
                    ]),
                ],
            ],
            'containerOptions' => [
                'class' => 'col-sm-12',
            ],
            'position' => TabsX::POS_ABOVE,
            'bordered' => false,
            'encodeLabels' => false,
        ]);
        ?>
    </fieldset>

    <div style="height: 15px;">&nbsp;</div>

    <fieldset>
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?= $form->field($model, 'contact_phone')->label('Телефон') ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?= $form->field($model, 'contact_fax')->label('Факс') ?>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?= $form->field($model, 'contact_email')->input('email')->label('E-mail') ?>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?= $form->field($model, 'contact_site')->label('Сайт URL') ?>
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset>
        <div class="row">
            <div class="col-sm-6">
                <div class="col-sm-12">
                    <?= $form->field($model, 'logo_file_name')
                        ->dropDownList(
                            MediaFileHelper::findByPattern('ORGANIZATION_LOGO_DIR', 'images', 'assoc'),
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
                    <?= $form->field($model, 'stamp_file_name')
                        ->dropDownList(
                            MediaFileHelper::findByPattern('STAMP_DIR', 'images', 'assoc'),
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