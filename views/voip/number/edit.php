<?php
/**
 * Создание/редактирование номера
 *
 * @var \yii\web\View $this
 * @var NumberForm $formModel
 */

use app\classes\voip\forms\NumberForm;
use app\models\DidGroup;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$number = $formModel->number;

if (!$number->isNewRecord) {
    $this->title = $number->number;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        'Телефония',
        ['label' => 'Номера', 'url' => $cancelUrl = '/voip/number'],
        $this->title
    ],
]) ?>

<div class="well">
    <?php $form = ActiveForm::begin(); ?>

    <?php
    // сообщение об ошибке
    if ($formModel->validateErrors) {
        Yii::$app->session->setFlash('error', $formModel->validateErrors);
    }
    ?>

    <div class="row">

        <?php // Статус ?>
        <div class="col-sm-3">
            <?= $form->field($number, 'status')
                ->widget(Select2::className(), [
                    'data' => \app\models\Number::dao()->getStatusList($isWithEmpty = $number->isNewRecord),
                ]) ?>
        </div>

        <?php // Красивость ?>
        <div class="col-sm-3">
            <?= $form->field($number, 'beauty_level')
                ->widget(Select2::className(), [
                    'data' => DidGroup::dao()->getBeautyLevelList($isWithEmpty = $number->isNewRecord),
                ]) ?>
        </div>

        <?php // DID-группа ?>
        <div class="col-sm-3">
            <?= $form->field($number, 'did_group_id')
                ->widget(Select2::className(), [
                    'data' => DidGroup::dao()->getList($isWithEmpty = $number->isNewRecord, $number->city_id),
                ]) ?>
        </div>

        <?php // Тех. номер ?>
        <div class="col-sm-3">
            <?= $form->field($number, 'number_tech') ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($number->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
