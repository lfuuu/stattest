<?php
/**
 * Создание/редактирование региона
 *
 * @var BaseView $this
 * @var RegionForm $formModel
 */

use app\classes\BaseView;
use app\classes\dictionary\forms\RegionForm;
use app\models\Country;
use app\models\Timezone;
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
        'Словари',
        ['label' => 'Регионы (точки подключения)', 'url' => $cancelUrl = '/dictionary/region'],
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

        <?php // Название ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'name')->textInput() ?>
        </div>

        <?php // Короткое название ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'short_name')->textInput() ?>
        </div>

        <?php // Часовой пояс ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'timezone_name')->dropDownList(Timezone::getList()) ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($region, 'is_active', ['options' => ['class' => 'pull-left', 'style'=> 'margin-right: 20px']])->checkbox() ?>
            <?= $form->field($region, 'is_use_sip_trunk')->checkbox() ?>
            <?= $form->field($region, 'is_use_vpbx')->checkbox() ?>
        </div>

    </div>

    <div class="row">

        <?php // ID ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'id')->textInput() ?>
        </div>

        <?php // Код ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'code')->textInput() ?>
        </div>

        <?php // Страна ?>
        <div class="col-sm-3">
            <?= $form->field($region, 'country_id')
                ->widget(Select2::class, [
                    'data' => Country::getList($isWithEmpty = $region->isNewRecord),
                ]) ?>
        </div>

        <div class="col-sm-3">
            <?= $form->field($region, 'type_id')
                ->widget(Select2::class, [
                    'data' => \app\models\Region::$typeNames,
                ]) ?>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($region->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
