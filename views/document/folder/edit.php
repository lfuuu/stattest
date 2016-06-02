<?php

use app\widgets\JQTree\JQTreeInput;
use yii\data\ActiveDataProvider;
use yii\widgets\Breadcrumbs;
use kartik\widgets\ActiveForm;
use yii\helpers\Url;
use app\classes\Html;
use app\models\document\DocumentFolder;

$cancelUrl = Url::toRoute(['/document/template/index']);

/** @var $dataProvider ActiveDataProvider */
/** @var DocumentFolder $model */

echo Html::formLabel('Управление шаблонами документов');

echo Breadcrumbs::widget([
    'links' => [
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
    <div class="col-sm-6">
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
    <div class="col-sm-2">
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
