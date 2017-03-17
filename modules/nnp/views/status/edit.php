<?php
/**
 * Создание/редактирование статусов
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 */

use app\modules\nnp\forms\status\Form;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$status = $formModel->status;

if (!$status->isNewRecord) {
    $this->title = $status->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Статусы направлений', 'url' => $cancelUrl = '/nnp/status/'],
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
            <?= $form->field($status, 'name')->textInput() ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($status->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
