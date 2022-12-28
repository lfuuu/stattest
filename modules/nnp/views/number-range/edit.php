<?php
/**
 * Редактирование диапазона номеров
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use app\modules\nnp\forms\numberRange\Form;
use app\modules\nnp\models\City;
use app\modules\nnp\models\NdcType;
use app\modules\nnp\models\Operator;
use app\modules\nnp\models\Region;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$numberRange = $formModel->numberRange;

if (!$numberRange->isNewRecord) {
    $this->title = $numberRange->ndc_str . ' ' . $numberRange->number_from . ' - ' . $numberRange->number_to;
} else {
    $this->title = Yii::t('common', 'Create');
}
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план', 'url' => '/nnp/'],
        ['label' => 'Диапазон номеров', 'url' => $cancelUrl = '/nnp/number-range/'],
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

        <?php // Номер от и до ?>
        <div class="col-sm-2">
            <label>Диапазон номеров</label>
            <div>
                <?= $numberRange->full_number_from ?><br/>
                <?= $numberRange->full_number_to ?>
            </div>
        </div>

        <?php // Кол-во номеров ?>
        <div class="col-sm-2">
            <label>Кол-во номеров</label>
            <div><?= 1 + $numberRange->number_to - $numberRange->number_from ?></div>
        </div>

        <?php // Вкл. ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('is_active') ?></label>
            <div><?= Yii::t('common', $numberRange->is_active ? 'Yes' : 'No') ?></div>
        </div>

        <?php // Дата добавления ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('insert_time') ?></label>
            <div>
                <?= $numberRange->insert_time ?
                    (new \app\classes\DateTimeWithUserTimezone($numberRange->insert_time))->getDateTime() :
                    Yii::t('common', '(not set)') ?>
            </div>
        </div>

        <?php // Дата редактирования ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('update_time') ?></label>
            <div>
                <?= $numberRange->insert_time ?
                    (new \app\classes\DateTimeWithUserTimezone($numberRange->update_time))->getDateTime() :
                    Yii::t('common', '(not set)') ?>
            </div>
        </div>

        <?php // Дата выключения ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('date_stop') ?></label>
            <div><?=
                $numberRange->date_stop ?
                    Yii::$app->formatter->asDate($numberRange->date_stop, 'medium') :
                    Yii::t('common', '(not set)') ?>
            </div>
        </div>

    </div>
    <br/>

    <div class="row">

        <?php // Страна ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('country_code') ?></label>
            <div><?= $numberRange->country ? $numberRange->country->name_rus : '' ?></div>
        </div>

        <?php // Статус номера ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('status_number') ?></label>
            <div><?= $numberRange->status_number ?></div>
        </div>

        <?php // Дата принятия решения о выделении диапазона ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('date_resolution') ?></label>
            <div><?= $numberRange->date_resolution ? Yii::$app->formatter->asDate($numberRange->date_resolution, 'medium') : '' ?></div>
        </div>

        <?php // Комментарий или номер решения о выделении диапазона ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('detail_resolution') ?></label>
            <div><?= $numberRange->detail_resolution ?></div>
        </div>

    </div>
    <br/>

    <div class="row">

        <?php // Исходный регион ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('region_source') ?></label>
            <div><?= htmlspecialchars($numberRange->region_source) ?></div>
        </div>

        <?php // Исходный город ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('city_source') ?></label>
            <div><?= htmlspecialchars($numberRange->city_source) ?></div>
        </div>

        <?php // Исходный оператор ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('operator_source') ?></label>
            <div><?= htmlspecialchars($numberRange->operator_source) ?></div>
        </div>

        <?php // исходный тип NDC ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('ndc_type_source') ?></label>
            <div><?= $numberRange->ndc_type_source ?></div>
        </div>

    </div>
    <br/>

    <div class="row">

        <?php // Регион ?>
        <div class="col-sm-3">
            <?= $form->field($numberRange, 'region_id')->widget(Select2::class, [
                'data' => Region::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $numberRange->country_code),
            ]) ?>
            <div>
                <?= ($region = $numberRange->region) ?
                    Html::a($region->name, $region->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php // Город ?>
        <div class="col-sm-3">
            <?= $form->field($numberRange, 'city_id')->widget(Select2::class, [
                'data' => City::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $numberRange->country_code),
            ]) ?>
            <div>
                <?= ($city = $numberRange->city) ?
                    Html::a($city->name, $city->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php // Оператор ?>
        <div class="col-sm-3">
            <?= $form->field($numberRange, 'operator_id')->widget(Select2::class, [
                'data' => Operator::getList($isWithEmpty = true, $isWithNullAndNotNull = false, $numberRange->country_code),
            ]) ?>
            <div>
                <?= ($operator = $numberRange->operator) ?
                    Html::a($operator->name, $operator->getUrl()) :
                    '' ?>
            </div>
        </div>

        <?php // Тип NDC ?>
        <div class="col-sm-3">
            <?= $form->field($numberRange, 'ndc_type_id')->widget(Select2::class, [
                'data' => NdcType::getList($isWithEmpty = true, $isWithNullAndNotNull = false),
            ]) ?>
            <div>
                <?= ($ndcType = $numberRange->ndcType) ?
                    Html::a($ndcType->name, $ndcType->getUrl()) :
                    '' ?>
            </div>
        </div>

    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($numberRange->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
