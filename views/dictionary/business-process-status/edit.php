<?php
/**
 * Создание/редактирование статуса
 *
 * @var \app\classes\BaseView $this
 * @var BusinessProcessStatusForm $formModel
 */

use app\classes\dictionary\forms\BusinessProcessStatusForm;
use app\models\BusinessProcess;
use app\classes\Html;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$status = $formModel->status;
echo Html::formLabel('Редактирование статуса бизнес процесса');
$breadCrumbLinks = [
    'Словари',
    ['label' => 'Статусы бизнес процессов', 'url' => $cancelUrl = '/dictionary/business-process-status'],
];

$businessProcesses = BusinessProcess::getListWithBusinessName($isWithEmpty = $status->isNewRecord);

if (!$status->isNewRecord) {
    $breadCrumbLinks[] = [
        'label' => $businessProcesses[$status->business_process_id],
        'url' => Url::to(['/dictionary/business-process-status', 'BusinessProcessStatusFilter' => ['business_process_id' => $status->business_process_id]])
    ];
    $breadCrumbLinks[] = $this->title = $status->name;
} else {
    $breadCrumbLinks[] = $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget(['links' => $breadCrumbLinks]) ?>

<div class="well">
    <?php
    $form = ActiveForm::begin();
    $viewParams = ['formModel' => $formModel,
        'form' => $form,];
    ?>

    <?php
    // сообщение об ошибке
    if ($formModel->validateErrors) {
        Yii::$app->session->setFlash('error', $formModel->validateErrors);
    }
    ?>

    <div class="row">

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($status, 'business_process_id')
                ->widget(Select2::class, ['data' => $businessProcesses,]) ?>
        </div>

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($status, 'name')->textInput() ?>
        </div>

        <?php // Старый статус ?>
        <div class="col-sm-2">
            <?= $form->field($status, 'oldstatus')->textInput() ?>
        </div>

        <?php // цвет ЛС ?>
        <div class="col-sm-2">
            <?= $form->field($status, 'color')->textInput(['type' => 'color']) ?>
        </div>

    </div>
    <div class="row">

        <?php // Отправка счета ?>
        <div class="col-sm-4">
            <?= $form->field($status, 'is_bill_send')->checkbox() ?>
        </div>

        <?php // Завершающий статус ?>
        <div class="col-sm-4">
            <?= $form->field($status, 'is_off_stage')->checkbox() ?>
        </div>

        <?php // Работает wizard ?>
        <div class="col-sm-4">
            <?= $form->field($status, 'is_with_wizard')->checkbox() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($status->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
