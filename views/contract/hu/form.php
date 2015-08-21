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
    ?>

</div>