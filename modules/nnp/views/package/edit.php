<?php
/**
 * Создание/редактирование направления
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 */

use app\modules\nnp\forms\package\Form;
use app\modules\nnp\models\Prefix;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$package = $formModel->package;

if (!$package->isNewRecord) {
    $this->title = $package->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Пакеты', 'url' => $cancelUrl = '/nnp/package/'],
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

    $prefixList = Prefix::getList(false);
    ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($package, 'name')->textInput() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($package->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$package->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
