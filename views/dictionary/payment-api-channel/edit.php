<?php
/**
 * @var BaseView $this
 * @var PaymentApiChannelForm $formModel
 */

use app\classes\BaseView;
use app\classes\dictionary\forms\PaymentApiChannelForm;
use app\models\PaymentApiChannel;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$region = $formModel->model;

if (!$region->isNewRecord) {
    $this->title = $region->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        'Словари',
        ['label' => PaymentApiChannel::TITLE, 'url' => $cancelUrl = '/dictionary/' . PaymentApiChannel::NAVIGATION],
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

        <?php // код канала ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'code')->textInput(['disabled' => $formModel->isCodeUsed]) ?>
        </div>

        <?php // Название ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'name')->textInput() ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($region, 'is_active', ['options' => ['class' => 'pull-left', 'style'=> 'margin-right: 20px']])->checkbox() ?>
        </div>

    </div>


    <div class="row">

        <?php // Название ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'access_token')->textInput() ?>
        </div>

        <?php // Чек от орагнизации ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'check_organization_id')->dropDownList(\app\models\Organization::dao()->getList(true), ['class' => 'select2']) ?>
        </div>

    </div>


    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($region->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
