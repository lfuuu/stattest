<?= app\classes\Html::formLabel($this->title = 'СОРМ: Адреса') ?>
<?= \yii\widgets\Breadcrumbs::widget([
    'links' => [
        ['label' => $this->title, 'url' => '/sorm/address/?hash=' . $hash],
    ],
]) ?>

<?php
$form = \yii\widgets\ActiveForm::begin([
    'method' => 'post',
    'action' => ['/sorm/address', 'hash' => $model->hash],
]);

$isNeedCheck = $model->state != 'need_check';

$option = [];

if ($isNeedCheck) {
    $option = ['disabled' => true];
}

echo \app\classes\Html::hiddenInput('doSave', 1);

?>

    <div class="well">
        <div class="row" style="margin-left:2%; margin-right:2%;">
            <div class="col-sm-6">
                <?= $form->field($model, "address")->textInput(['readonly' => true])?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "state")->dropDownList(\app\modules\sorm\models\pg\Address::getStateList(), $option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "post_code")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "country")->textInput($option)  ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">
            <div class="col-sm-2">
                <?= $form->field($model, "district_type")->textInput($option)  ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "district")->textInput($option)  ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "region_type")->textInput($option)  ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "region")->textInput($option)  ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "city_type")->textInput($option)  ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "city")->textInput($option)  ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">

            <div class="col-sm-2">
                <?= $form->field($model, "street_type")->textInput($option)  ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "street")->textInput($option)  ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "house")->textInput($option)  ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "housing")->textInput($option)  ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "flat_type")->textInput($option)  ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "flat")->textInput($option)  ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">
            <div class="col-sm-6">
                <?= $form->field($model, "unparsed_parts")->textInput(['readonly' => true]) ?>
            </div>
            <div class="col-sm-6" style="text-align: right;">
                <?= $this->render('//layouts/_submitButton' . 'Save') ?>
            </div>
        </div>
        <div class="row well" style="margin-left:2%; margin-right:2%;">

            <?php
            try {
            $j = \app\classes\Utils::fromJson($model->json);
            ?>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th scope="col">Название</th>
                    <th scope="col">Значение</th>
                </tr>
                </thead>
                <tbody>

                <?php
                foreach ($j as $f => $v) {
                    ?>
                    <tr>
                        <td><?= $f ?></td>
                        <td><?= (is_array($v) ? "<pre>" . var_export($v, true) . "</pre>" : $v) ?></td>
                    </tr>

                    <?php

                }
                ?>
                    </tbody>
                </table>
                <?php

                } catch (Exception $e) {
                    echo \app\classes\Html::tag('span', $e->getMessage(), ['class' => 'text-danger']);
                } ?>
        </div>

    </div>

    </div>

<?php
\yii\widgets\ActiveForm::end();
?>