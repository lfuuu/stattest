<?php
/**
 * Создание/редактирование регионы
 *
 * @var \yii\web\View $this
 * @var Form $formModel
 */

use app\modules\nnp\forms\region\Form;
use app\modules\nnp\models\Country;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$region = $formModel->region;

if (!$region->isNewRecord) {
    $this->title = $region->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Регионы', 'url' => $cancelUrl = '/nnp/region/'],
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

        <?php // Страна ?>
        <div class="col-sm-2">
            <?= $form->field($region, 'country_code')->widget(Select2::className(), [
                'data' => Country::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $indexBy = 'code'),
            ]) ?>
        </div>

        <?php // Название ?>
        <div class="col-sm-6">
            <?= $form->field($region, 'name')->textInput() ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group">
        <?= $this->render('//layouts/_submitButton' . ($region->isNewRecord ? 'Create' : 'Save')) ?>
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?php if (!$region->isNewRecord) : ?>
            <?= $this->render('//layouts/_submitButtonDrop') ?>
        <?php endif ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
