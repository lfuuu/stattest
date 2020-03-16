<?php

/**
 * @var \app\classes\BaseView $this
 * @var SBISDocument $model
 * @var string $indexUrl
 */

use app\classes\Html;
use app\modules\sbisTenzor\models\SBISDocument;
use kartik\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$this->title = Yii::t('common', 'Create');

?>

<?php

echo Html::formLabel($this->title);
echo Breadcrumbs::widget([
    'links' => [
        'СБИС',
        ['label' => 'Пакеты документов в СБИС', 'url' => $indexUrl],
        $this->title
    ],
]) ?>

<?php if ($model) : ?>
<?php
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
    'options' => [
        'enctype' => 'multipart/form-data',
    ],
]);
?>
<div class="well" style="background-color: white;">
<div class="well well-lg">
    <div class="row">
        <div class="col-sm-6">
            <label class="control-label" for="sbisdocument-sbis_organization_id">Организация-отправитель в СБИС</label>
            <br />
            <span><?= strval($model->sbisOrganization->organization->name) ?></span>
            <br /><br />
        </div>
    </div>
</div>
<div class="well well-lg">
    <div class="row">
        <div class="col-sm-6">
            <label class="control-label" for="sbisdocument-client_account_id">Клиент-получатель</label>
            <br />
            <span><?= $model->clientAccount->contragent->name_full . sprintf(' (%s)', $model->clientAccount->getName()) ?></span>
            <br /><br />
        </div>
    </div>
</div>
    <div class="row">
        <div class="col-sm-2">
            <?= $form->field($model, 'number')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'date')->widget(\kartik\widgets\DatePicker::class,
                [
                    'removeButton' => false,
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                    ]
                ]
            ) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'comment')->textInput() ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <label>Документы</label>
            <?php for ($i = 1; $i <= SBISDocument::MAX_ATTACHMENTS; $i++): ?>
                <input type="file" name="<?= $model->formName(); ?>[filename][<?= $i ?>]" class="media-manager" data-language="ru-RU" /><br /><br />
            <?php endfor; ?>
        </div>
    </div>

    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $indexUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . 'Create') ?>
    </div>

</div>
<?php ActiveForm::end(); ?>
<?php endif ?>
