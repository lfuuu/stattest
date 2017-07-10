<?php
/**
 * Создание/редактирование города
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\modules\nnp\forms\city\Form;
use app\modules\nnp\models\Country;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$city = $formModel->city;

if (!$city->isNewRecord) {
    $this->title = $city->name;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Города', 'url' => $cancelUrl = '/nnp/city/'],
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
            <?= $form->field($city, 'country_code')->widget(Select2::className(), [
                'data' => Country::getList($isWithEmpty = true),
            ]) ?>
        </div>

        <?php // Название ?>
        <div class="col-sm-4">
            <?= $form->field($city, 'name')->textInput() ?>
        </div>

        <?php // Название транслитом ?>
        <div class="col-sm-4">
            <?= $form->field($city, 'name_translit')->textInput() ?>
        </div>

        <?php // Кол-во ?>
        <div class="col-sm-2">
            <label><?= $city->getAttributeLabel('cnt') ?></label>
            <div><?= $city->cnt ?></div>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($city->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
