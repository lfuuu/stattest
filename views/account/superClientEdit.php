<?php

use app\classes\Html;
use kartik\widgets\ActiveForm;
use kartik\builder\Form;

?>
<div class="row">
    <div class="col-sm-12">
        <h2><?= ($model->isNewRecord) ? 'Создание' : 'Редактирование' ?> суперклиента</h2>

        <?php
        $f = ActiveForm::begin();
        ?>

        <div class="row" style="width: 1100px;">
            <?php

            echo '<div>';
            echo Form::widget([
                'model' => $model,
                'form' => $f,
                'columns' => 4,
                'attributeDefaults' => [
                    'container' => ['class' => 'col-sm-12'],
                    'type' => Form::INPUT_TEXT
                ],
                'attributes' => [
                    'name' => [],
                    'financial_manager_id' => [
                        'type' => Form::INPUT_DROPDOWN_LIST,
                        'items' => \app\models\User::getUserListByDepart(28, $isEnabled = true)
                    ],
                    'entry_point_id' => [
                        'type' => Form::INPUT_DROPDOWN_LIST,
                        'items' => \app\models\EntryPoint::getList($isWithEmpty = true),
                        'columnOptions' => [
                            'colspan' => 2,
                        ]
                    ],
                ],
            ]);

            echo '</div>';
            ?>

            <?php if (!$model->isNewRecord): ?>
            <div class="col-sm-12">
                <div class="form-group field-clienteditform-name">
                    <label class="control-label" for="clienteditform-name">Страна витрины: <?= $model->country->name ?></label>
                </div>
            </div>
            <?php endif; ?>


            <div class="col-sm-12 form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary', 'id' => 'buttonSave']); ?>
            </div>
            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
