<?php
/**
 * Создание/редактирование типа
 *
 * @var \app\classes\BaseView $this
 * @var \app\classes\uu\forms\serviceTypeForm $formModel
 */

use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$serviceType = $formModel->serviceType;
$this->title = $serviceType->isNewRecord ? Yii::t('common', 'Create') : $serviceType->name;
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => Yii::t('tariff', 'Service types'), 'url' => $cancelUrl = Url::to(['uu/service-type'])],
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
            <?= $form->field($serviceType, 'name')->textInput() ?>
        </div>
        <div class="col-sm-4">
            <?= $form->field($serviceType, 'close_after_days')->textInput() ?>
        </div>
    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($serviceType->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
