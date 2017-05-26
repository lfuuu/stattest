<?php
/**
 * Создание/редактирование статуса тарифа
 *
 * @var \app\classes\BaseView $this
 * @var \app\modules\uu\forms\tariffStatusForm $formModel
 */

use app\modules\uu\models\ServiceType;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$tariffStatus = $formModel->tariffStatus;
$this->title = $tariffStatus->isNewRecord ? Yii::t('common', 'Create') : $tariffStatus->name;
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => Yii::t('tariff', 'Tariff statuses'), 'url' => $cancelUrl = Url::to(['/uu/tariff-status'])],
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
            <?= $form->field($tariffStatus, 'name')->textInput() ?>
        </div>

        <div class="col-sm-4">
            <?= $form->field($tariffStatus, 'service_type_id')->widget(Select2::className(), [
                'data' => ServiceType::getList($isWithEmpty = true),
            ]) ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($tariffStatus->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
