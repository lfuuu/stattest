<?php

use app\widgets\JQTree\JQTreeInput;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use app\classes\Html;
use app\models\document\DocumentFolder;
use app\models\Business;

$cancelUrl = Url::toRoute(['/templates/document/template']);

/** @var $dataProvider ActiveDataProvider */
/** @var DocumentFolder $model */

echo Html::formLabel('Управление шаблонами документов');

echo Breadcrumbs::widget([
    'links' => [
        'Шаблоны',
        [
            'label' => 'Управление шаблонами документов',
            'url' => $cancelUrl,
        ],
        'Редактирование раздела'
    ],
]);

$form = ActiveForm::begin([]);
?>

<div class="row">
    <div class="col-sm-4">
        <?= $form->field($model, 'name')->textInput() ?>
    </div>
    <div class="col-sm-4">
        <?= $form->field($model, 'parent_id')->widget(JQTreeInput::class, [
            'data' => new DocumentFolder,
            'htmlOptions' => [
                'id' => 'treeview-input',
            ],
        ])
        ?>
    </div>
    <?php if(!is_null($model) && !$model->parent_id): ?>
        <div class="col-sm-3">
            <?= $form->field($model, 'default_for_business_id')->dropDownList(Business::getList(true)) ?>
        </div>
    <?php endif; ?>
    <div class="col-sm-1">
        <?= $form->field($model, 'sort')->textInput() ?>
    </div>
</div>

<?php // кнопки ?>
<div class="form-group">
    <?= $this->render('//layouts/_submitButtonSave') ?>
    <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
</div>

<?php
echo $form->field($model, 'id')->hiddenInput()->label('');
ActiveForm::end();
?>
