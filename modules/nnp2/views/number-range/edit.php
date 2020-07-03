<?php
/**
 * Редактирование диапазона номеров
 *
 * @var \app\classes\BaseView $this
 * @var Form $formModel
 */

use app\classes\Html;
use yii\helpers\Url;
use app\modules\nnp2\forms\numberRange\Form;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

$numberRange = $formModel->numberRange;

if (!$numberRange->isNewRecord) {
    $this->title = $numberRange->ndc . ' ' . $numberRange->number_from . ' - ' . $numberRange->number_to;
} else {
    $this->title = Yii::t('common', 'Create');
}

$geoPlace = $numberRange->geoPlace;
$region = $numberRange->region;
$city = $numberRange->city;
$operator = $numberRange->operator;
$ndcType = $numberRange->ndcType;

$prevHtml = '-';
if ($previous = $numberRange->previous) {
    $prevHtml = '';
    if ($previous->is_valid) {
        $prevHtml .= Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
    } else {
        $prevHtml .= Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
    }

    $prevHtml .= Html::a(
        sprintf(
            "%s %s-%s<br />%s<br />%s<br />%s",
            $previous->ndc,
            $previous->number_from,
            $previous->number_to,
            strval($previous->geoPlace),
            $previous->operator ? $previous->operator->name : '-',
            $previous->ndcType ? $previous->ndcType->name : '-'
        ),
        Url::to($previous->getUrl())
    );
};
?>

<?= Breadcrumbs::widget([
    'links' => [
        ['label' => 'Национальный номерной план 2.0', 'url' => '/nnp2/'],
        ['label' => 'Диапазон номеров', 'url' => $cancelUrl = '/nnp2/number-range/'],
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
                <?= $numberRange->number_from ?><br/>
                <?= $numberRange->number_to ?>
            </div>
        </div>

        <?php // Кол-во номеров ?>
        <div class="col-sm-2">
            <label>Кол-во номеров</label>
            <div><?= 1 + $numberRange->number_to - $numberRange->number_from ?></div>
        </div>

        <?php // Вкл. ?>
        <div class="col-sm-1">
            <label><?= $numberRange->getAttributeLabel('is_active') ?></label>
            <div>
                <?
                    if ($numberRange->is_active) {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
                    } else {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
                    }
                ?>
                <?= Yii::t('common', $numberRange->is_active ? 'Yes' : 'No') ?>
            </div>
        </div>

        <?php // Подтвержден ?>
        <div class="col-sm-1">
            <label><?= $numberRange->getAttributeLabel('is_valid') ?></label>
            <div>
                <?
                    if ($numberRange->is_valid) {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
                    } else {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
                    }
                ?>
                <?= Yii::t('common', $numberRange->is_valid ? 'Yes' : 'No') ?>
            </div>
        </div>

        <?php // Дата добавления ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('insert_time') ?></label>
            <div>
                <?= $numberRange->insert_time ?
                    Yii::$app->formatter->asDate($numberRange->insert_time, 'medium') :
                    Yii::t('common', '(not set)') ?>
            </div>
        </div>

        <?php // Дата редактирования ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('update_time') ?></label>
            <div>
                <?= $numberRange->insert_time ?
                    Yii::$app->formatter->asDate($numberRange->update_time, 'medium') :
                    Yii::t('common', '(not set)') ?>
            </div>
        </div>

        <?php // Дата выключения ?>
        <div class="col-sm-2">
            <label><?= $numberRange->getAttributeLabel('allocation_date_stop') ?></label>
            <div><?=
                $numberRange->allocation_date_stop ?
                    Yii::$app->formatter->asDate($numberRange->allocation_date_stop, 'medium') :
                    Yii::t('common', '(not set)') ?>
            </div>
        </div>

    </div>

    <div class="row">
        <br/>
    </div>

    <div class="row">
        <?php // Местоположение ?>
        <div class="col-sm-6">
            <label><?= $numberRange->getAttributeLabel('geo_place_id') ?></label>
            <div>
                <?
                    if ($geoPlace->is_valid) {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
                    } else {
                        echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
                    }
                ?>
                <?= $geoPlace ? Html::a(
                    strval($geoPlace),
                    Url::to($geoPlace->getUrl())
                ) : '-'; ?>
            </div>
        </div>

        <?php // Исходный оператор ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('operator_id') ?></label>
            <div>
                <?
                    if ($operator) {
                        if ($operator->is_valid) {
                            echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
                        } else {
                            echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
                        }
                    }
                ?>

                <?= $operator ? Html::a(
                    $operator->name,
                    Url::to($operator->getUrl())
                ) : '-' ?>
            </div>
        </div>

        <?php // исходный тип NDC ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('ndc_type_id') ?></label>
            <div>
                <?
                    if ($ndcType) {
                        if ($ndcType->is_valid) {
                            echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-ok text-success']) . '&nbsp;';
                        } else {
                            echo Html::tag('i', '', ['class' => 'glyphicon glyphicon-remove text-danger']) . '&nbsp;';
                        }
                    }
                ?>
                <?= $ndcType ? Html::a(
                    $ndcType->name,
                    Url::to($ndcType->getUrl())
                ) : '-' ?>
            </div>
        </div>
    </div>

    <div class="row">
        <br/>
    </div>

    <div class="row">

        <?php // Страна ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('geoPlace.country_code') ?></label>
            <div><?= $numberRange->country ? $numberRange->country->name_rus : '' ?></div>
        </div>

        <?php // Статус номера ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('comment') ?></label>
            <div><?= $numberRange->comment ?></div>
        </div>

        <?php // Дата принятия решения о выделении диапазона ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('allocation_date_start') ?></label>
            <div><?= $numberRange->allocation_date_start ? Yii::$app->formatter->asDate($numberRange->allocation_date_start, 'medium') : '' ?></div>
        </div>

        <?php // Комментарий или номер решения о выделении диапазона ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('allocation_reason') ?></label>
            <div><?= $numberRange->allocation_reason ?></div>
        </div>

    </div>

    <div class="row">
        <br/>
    </div>

    <div class="row">

        <?php // Исходный регион ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('geoPlace.region_id') ?></label>
            <div><?= $region ? Html::a(
                    $region->name,
                    Url::to($region->getUrl())
                ) : '-'; ?>
            </div>
        </div>

        <?php // Исходный город ?>
        <div class="col-sm-3">
            <label><?= $numberRange->getAttributeLabel('geoPlace.city_id') ?></label>
            <div><?= $city ? Html::a(
                    $city->name,
                    Url::to($city->getUrl())
                ) : '-' ?>
            </div>
        </div>

    </div>

    <div class="row">
        <br/>
    </div>

    <div class="row">

        <?php // Исходный диапазон ?>
        <div class="col-sm-12">
            <label><?= $numberRange->getAttributeLabel('previous_id') ?></label>
            <div><?= $prevHtml ?>
            </div>
        </div>

    </div>

    <div class="row">
        <br/>
    </div>

    <?php // кнопки ?>
    <div class="form-group text-right">
        <?= $this->render('//layouts/_buttonCancel', ['url' => $cancelUrl]) ?>
        <?= $this->render('//layouts/_submitButton' . ($numberRange->isNewRecord ? 'Create' : 'Save')) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
