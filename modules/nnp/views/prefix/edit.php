<?php
/**
 * Создание/редактирование префикса
 *
 * @var \yii\web\View $this
 * @var PrefixForm $formModel
 */

use app\modules\nnp\forms\PrefixForm;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$prefix = $formModel->prefix;

if (!$prefix->isNewRecord) {
    $this->title = $prefix->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Операторы', 'url' => $cancelUrl = '/nnp/prefix/'],
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

    <?php
    // сообщение об ошибке
    if ($formModel->validateErrors) {
        Yii::$app->session->setFlash('error', $formModel->validateErrors);
    }
    ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-6">
            <?= $form->field($prefix, 'name')->textInput() ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($prefix->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$prefix->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
