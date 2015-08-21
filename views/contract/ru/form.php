<?php
use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\builder\Form;
?>

<div class="row" style="width: 1100px;">
    <?php
    if(isset($contragents) && isset($contragentsOptions)) {
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 1,
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'contragent_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $contragents,
                    'options' => [
                        'options' => $contragentsOptions,
                    ],
                ],
            ],
        ]);
    }

    if ($model->isNewRecord) {
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'contract_type_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\ContractType::getList(),
                ],
                'organization_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $model->getOrganizationsList()],
            ],
        ]);
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 2,
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'manager' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['manager'] . '</label>'
                        . Select2::widget([
                            'model' => $model,
                            'attribute' => 'manager',
                            'data' => [],
                            'options' => [
                                'placeholder' => 'Начните вводить фамилию',
                                'data-current-value' => $model->manager ?: 0,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])
                        . '</div>'
                ],
                'account_manager' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['account_manager'] . '</label>'
                        . Select2::widget([
                            'model' => $model,
                            'attribute' => 'account_manager',
                            'data' => [],
                            'options' => [
                                'placeholder' => 'Начните вводить фамилию',
                                'data-current-value' => $model->account_manager ?: 0,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])
                        . '</div>'
                ],
            ],
        ]);
    }
    else {
        echo Form::widget([
            'model' => $model,
            'form' => $f,
            'columns' => 3,
            'attributeDefaults' => [
                'container' => ['class' => 'col-sm-12'],
                'type' => Form::INPUT_TEXT
            ],
            'attributes' => [
                'contract_type_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => \app\models\ContractType::getList(),
                ],
                //'state' => ['type' => Form::INPUT_DROPDOWN_LIST, "items" => ClientContract::$states],
                'state' => [
                    'type' => Form::INPUT_RAW,
                    'value' => function () use ($f, $model) {
                        $res = '<div class="col-sm-12">';
                        $res .= $f->field($model, 'state')->begin();
                        $res .= Html::activeLabel($model, 'state', ['class' => 'control-label']); //label
                        $res .= Html::activeDropDownList(
                            $model,
                            'state', $model->model->statusesForChange(),
                            [
                                'class' => 'form-control ' . $model->state,
                            ]
                        ); //Field
                        $res .= Html::error($model, 'state', ['class' => 'help-block', 'encode' => false]); //error
                        $res .= $f->field($model, 'state')->end();
                        $res .= '</div>';
                        return $res;
                    },
                ],
                'is_external' => [
                    'type' => Form::INPUT_CHECKBOX,
                    'options' => [
                        'container' => ['style' => 'margin-top: 25px;'],
                    ],
                ],
                'organization_id' => ['type' => Form::INPUT_DROPDOWN_LIST, 'items' => $model->getOrganizationsList()],
                'manager' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['manager'] . '</label>'
                        . Select2::widget([
                            'model' => $model,
                            'attribute' => 'manager',
                            'data' => [],
                            'options' => [
                                'placeholder' => 'Начните вводить фамилию',
                                'data-current-value' => $model->manager ?: 0,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])
                        . '</div>'
                ],
                'account_manager' => [
                    'type' => Form::INPUT_RAW,
                    'value' => '<div class="col-sm-12" style="padding-bottom: 15px;"><label>' . $model->attributeLabels()['account_manager'] . '</label>'
                        . Select2::widget([
                            'model' => $model,
                            'attribute' => 'account_manager',
                            'data' => [],
                            'options' => [
                                'placeholder' => 'Начните вводить фамилию',
                                'data-current-value' => $model->account_manager ?: 0,
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])
                        . '</div>'
                ],
            ],
        ]);
    }
    ?>

</div>