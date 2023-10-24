<?php

/** @var string $hash */
/** @var \app\modules\sorm\models\pg\Address $model */

?>
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

$isNeedCheck = $model->state == 'need_check';

$option = [];

if (!$isNeedCheck) {
    $option = ['disabled' => true];
}

echo \app\classes\Html::hiddenInput('doSave', 1);

?>

    <div class="row" style="padding-left: 15px; display: none;" id="instruction">
        <div class="col-md-8 alert alert-info ">
            <p>Данный интерфейс предназначен для ручной проверки и корректировки адреса.</p>
            <p>Для чего это всё надо: Организация-посредник при получении от "нас" информации проверяет её. В частности
                проверяет и адреса. Через DaData. </p>
            <p>Если dadata не распознает адрес, или есть сомнения в его правильности, выгрузка не происходит. Служба
                мониторинга сообщает что есть ошибки и приходиться разработчику выискивать причину и решать её. Из-за
                отсутствия выгруженого контрагента, ошибки возникают в последующих выгрузках. (Услуги, пользователи и
                т.д.)</p>
            <p>Есть алтернативный вариант. Когда выгрузка проходит с уже разбитыми на составляющие элементы
                адресами. </p>
            <hr>
            <p>Контрагент с не заполнеными адресами, или адресами в которых есть сомнения - не выгружается.</p>
            <p>Всегда работает автоматическое распознавание адреса. Если у автоматического распознавания есть сомнения -
                адрес помечаются на ручную проверку.</p>
            <p>Ручная проверка состоит из:</p>
            <p>1. Основная проверка: правильно ли распознан населеный пункт, в том районе/городе/области/крае.</p>
            <p>2. Правильно ли распознан номер дома и помещение в нем.</p>
            <p>Дадата не всегда корректно распознает буквы в номерах домов, офисов и квартир. Если увидели - вносите их
                руками.</p>
            <p>Исключительные ситуации:<br></p>
            <div>1. Есть дополнительная адресация дома. Допустим есть "литера". В поле "корпус", после номера корпуса,
                если такой есть пишем "литера А".
            </div>
            <div>2. Есть дополнительная адресация помещения. Есть помещение, и в нем офис. <small>191123,
                    Г.Санкт-Петербург, ПЕР. МАНЕЖНЫЙ, Д. 14, ЛИТЕР А, ПОМЕЩ. 1Н, ОФИС № 201-209</small>.
                Тип помещения: ПОМЕЩ., номер помещения: 1Н, ОФИС № 201-209
            </div>
            <hr>
            <p>После проверки и корректировки адреса устанавливаем "Состояние" в "Всё ОК"</p>
        </div>
    </div>
<?php
echo $this->render('//layouts/_toggleButton', ['divSelector' => '#instruction', 'title' => 'Инструкция'])
?>
    <div class="well">
        <div class="row" style="margin-left:2%; margin-right:2%;">
            <div class="col-sm-6">
                <?= $form->field($model, "address")->textInput(['readonly' => true]) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "state")->dropDownList(\app\modules\sorm\models\pg\Address::getStateList(), $option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "post_code")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "country")->textInput($option) ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">
            <div class="col-sm-2">
                <?= $form->field($model, "district_type")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "district")->textInput($option) ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "region_type")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "region")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "city_type")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "city")->textInput($option) ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">

            <div class="col-sm-2">
                <?= $form->field($model, "street_type")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "street")->textInput($option) ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "house")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "housing")->textInput($option) ?>
            </div>

            <div class="col-sm-2">
                <?= $form->field($model, "flat_type")->textInput($option) ?>
            </div>
            <div class="col-sm-2">
                <?= $form->field($model, "flat")->textInput($option) ?>
            </div>
        </div>
        <div class="row" style="margin-left:2%; margin-right:2%;">
            <div class="col-sm-6">
                <?= $form->field($model, "unparsed_parts")->textInput(['readonly' => true]) ?>
            </div>
            <div class="col-sm-6" style="text-align: right;">
                <?= ($isNeedCheck ? $this->render('//layouts/_submitButton' . 'Save') : '') ?>
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
    <?= $this->render('//layouts/_showHistory', ['model' => $model]) ?>
<?php
\yii\widgets\ActiveForm::end();
?>