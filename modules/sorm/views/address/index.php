<?= app\classes\Html::formLabel($this->title = 'СОРМ: Адреса') ?>
<?= \yii\widgets\Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/sorm/address/?hash=' . $hash],
    ],
]) ?>

<?php
$form = \yii\widgets\ActiveForm::begin([
    'method' => 'get',
    'action' => ['controller/action'],
]);

?>

    <div class="well">
        <div class="row" style="margin-left:2%; margin-right:2%;">

            <div class="col-sm-4">
                <?= $form->field($model, "address") ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, "state")->dropDownList(\app\modules\sorm\models\pg\Address::getStateList()) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "post_code") ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "country") ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">
            <div class="col-sm-2">
                <?= $form->field($model, "district_type") ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "district") ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "region_type") ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "region") ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "city_type") ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "city") ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">

            <div class="col-sm-2">
                <?= $form->field($model, "street_type") ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "street") ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "house") ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "housing") ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "flat_type") ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "flat") ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">
            <div class="col-sm-6">
                <?= $form->field($model, "unparsed_parts") ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">
        <pre>
            <?= print_r(\app\classes\Utils::fromJson($model->json)) ?>
                </pre>
        </div>

    </div>

<?php
\yii\widgets\ActiveForm::end();
?>