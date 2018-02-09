<?php
/**
 * Создание/редактирование VM-тарифа
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\tariffVmForm $formModel
 */

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$tariffVm = $formModel->tariffVm;
$this->title = $tariffVm->isNewRecord ? Yii::t('common', 'Create') : $tariffVm->name;
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => Yii::t('tariff', 'Tariff VMs'), 'url' => $cancelUrl = Url::to(['/uu/tariff-vm'])],
        $this->title,
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

        <div class="col-sm-4">
            <?= $form->field($tariffVm, 'id')->textInput(['type' => 'number', 'step' => 1]) ?>
        </div>

        <div class="col-sm-4">
            <?= $form->field($tariffVm, 'name')->textInput() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($tariffVm->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
