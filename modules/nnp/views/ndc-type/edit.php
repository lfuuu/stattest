<?php
/**
 * Создание/редактирование типа NDC
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 */

use app\modules\nnp\forms\ndcType\Form;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$ndcType = $formModel->ndcType;

if (!$ndcType->isNewRecord) {
    $this->title = $ndcType->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Типы NDC', 'url' => $cancelUrl = '/nnp/ndc-type/'],
        $this->title
    ],
]) ?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    $viewParams = [
        'formModel' => $formModel,
        'form' => $form,
    ];
    ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-6">
            <?= $form->field($ndcType, 'name')->textInput() ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($ndcType->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$ndcType->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
