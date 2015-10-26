<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use kartik\file\FileInput;
use yii\helpers\Html;
use app\helpers\DateTimeZoneHelper;

/** @var RequestOnlimeStateForm $model */
/** @var \app\models\Bill $bill */

$billItems = $bill->lines;
$billExtendsInfo = $bill->extendsInfo;
$stages = $operator->report->getTroubleStages($trouble->id);
$model->state_id = $trouble->currentStage->state_id;

list(, $operator_name, $partner) = preg_split('#:\s*#', $billExtendsInfo->comment2);
?>

<link href="/css/behaviors/media-manager.css" rel="stylesheet" />

<div class="well">
    <legend>Просмотр счета №<?= $bill->bill_no; ?></legend>

    <legend style="font-size: 16px;">Данные счета</legend>
    <div class="col-xs-12">
        <div class="col-xs-3">
            <b>Ф.И.О.</b><br />
            <?= $billExtendsInfo->fio; ?>
        </div>
        <div class="col-xs-3">
            <b>Адрес доставки</b><br />
            <?= $billExtendsInfo->address; ?>
        </div>
        <div class="col-xs-2">
            <b>Контактный телефон</b><br />
            <?= $billExtendsInfo->phone; ?>
        </div>
        <div class="col-xs-2">
            <b>Ф.И.О. оператора</b><br />
            <?= $operator_name; ?>
        </div>
        <div class="col-xs-2">
            <b>Партнер</b><br />
            <?= $partner; ?>
        </div>
        <div class="col-xs-2">
            <b>Временной интервал</b><br />
            <?= $billExtendsInfo->comment1; ?>
        </div>
        <div class="col-xs-12" style="margin-top: 15px;">
            <label style="float: left;"><b>Комментарий</b></label>
            <div style="float: left; margin-left: 10px; margin-top: 2px; background: url('/images/icons/edit.gif') no-repeat 0 0; width: 16px; height: 16px;">
                <a href="#" data-edit="#bill-comment" class="switchEditable" style="margin-left: 22px;">Редактировать</a>
            </div>
            <div style="clear: both;"></div>
            <div id="bill-comment" style="display: none;">
                <?php
                $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_VERTICAL,
                ]);

                echo Html::hiddenInput('scenario', 'setComment');
                ?>

                <fieldset>
                    <textarea class="form-control" name="comment"><?= $bill->comment; ?></textarea>
                </fieldset>

                <?php
                echo Form::widget([
                    'model' => $model,
                    'form' => $form,
                    'columns' => 2,
                    'attributes' => [
                        'empty1' => [
                            'type' => Form::INPUT_RAW,
                            'value' =>
                                Html::tag(
                                    'div',
                                    Html::submitButton('Сохранить', [
                                        'class' => 'btn btn-primary',
                                        'style' => 'width: 100px;',
                                    ]),
                                    [
                                        'style' => 'padding-top: 20px; text-align: right;',
                                    ]
                                )
                        ]
                    ],
                ]);

                ActiveForm::end();
                ?>
            </div>
            <span>
                <?= $bill->comment; ?>
            </span>
        </div>
    </div>
    <div style="clear: both;">&nbsp;</div>

    <?php if (count($billItems)): ?>
        <legend style="font-size: 16px;">Позиции счета</legend>
        <table class="table table-hover table-condensed table-striped">
            <colgroup>
                <col width="2%" />
                <col width="*" />
                <col width="20%" />
                <col width="10%" />
            </colgroup>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Наименование</th>
                    <th>Количество</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($billItems as $number => $item): ?>
                    <tr>
                        <td><?= ($number + 1); ?></td>
                        <td><?= $item->item; ?></td>
                        <td><?= (int) $item->amount; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if (count($stages)): ?>
        <legend style="font-size: 16px;">Этапы</legend>
        <table class="table table-hover table-condensed table-striped">
            <thead>
                <tr>
                    <th>Состояние</th>
                    <th>Ответственный</th>
                    <th>сроки</th>
                    <th>Этап закрыл</th>
                    <th>с комментарием</th>
                    <th>время закрытия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stages as $stage): ?>
                    <tr>
                        <td><?= $stage['state_name']; ?></td>
                        <td><?= $stage['user_main']; ?></td>
                        <td>
                            <?php
                            $dateStartView = $stage['date_start'];
                            $dateEndView = $stage['date_finish_desired'];

                            if (!preg_match('#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#', $dateStartView)) {
                                $dateStartView = DateTimeZoneHelper::getDateTime($dateStartView, 'Y-m-d');
                            }
                            if (!preg_match('#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#', $dateEndView)) {
                                $dateEndView = DateTimeZoneHelper::getDateTime($dateEndView, 'Y-m-d');
                            }
                            ?>

                            <?= $dateStartView; ?><br />
                            <?= $dateEndView; ?>
                        </td>
                        <td><?= $stage['user_edit']; ?></td>
                        <td><?= $stage['comment']; ?></td>
                        <td>
                            <?php
                            $dateCloseView = $stage['date_edit'];
                            if (!preg_match('#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#', $dateStartView)) {
                                $dateStartView = DateTimeZoneHelper::getDateTime($dateStartView, 'Y-m-d');
                            }
                            echo $dateCloseView;
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <legend>Документы</legend>
    <?php
    $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_VERTICAL,
        'options' => [
            'enctype' => 'multipart/form-data',
        ],
    ]);

    $files = $trouble->mediaManager->getFiles();
    ?>

    <?php if (count($files)): ?>
        <div class="media-list">
            <?php foreach ($files as $file): ?>
                <div data-model="troubles" data-file-id="<?= $file['id']; ?>" data-mime-type="<?= $file['mimeType']; ?>'"><?= $file['name']; ?></div>
            <?php endforeach; ?>
        </div>
        <div style="clear: both;"></div>
    <?php endif; ?>

    <?php
    echo Html::hiddenInput('scenario', 'setFiles');
    echo FileInput::widget([
        'model' => $model,
        'name' => 'files[]',
        'options' => [
            'multiple' => true,
            'accept' => 'image/*',
        ],
        'pluginOptions' => [
            'showCaption' => false,
            'showRemove' => false,
            'showUpload' => false,
            'browseClass' => 'btn btn-default btn-block',
            'browseIcon' => '<i></i> ',
            'browseLabel' =>  'Выбрать файл',
            'initialPreview' => [],
        ],
    ]);

    echo Form::widget([
        'model' => $model,
        'form' => $form,
        'columns' => 2,
        'attributes' => [
            'empty1' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::submitButton('Сохранить', [
                            'class' => 'btn btn-primary',
                            'style' => 'width: 100px;',
                        ]),
                        [
                            'style' => 'padding-top: 20px; text-align: right;',
                        ]
                    )
            ]
        ],
    ]);

    ActiveForm::end();
    ?>

    <?php if ($model->state_id == 24): // Отложен ?>
        <legend style="font-size: 16px;">Этап</legend>
        <?php
        $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_VERTICAL,
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 1,
            'attributes' => [
                'comment' => ['type' => Form::INPUT_TEXTAREA],
            ],
        ]);

        echo Form::widget([
            'model' => $model,
            'form' => $form,
            'columns' => 3,
            'attributes' => [
                'state_id' => [
                    'type' => Form::INPUT_DROPDOWN_LIST,
                    'items' => $operator->getAvailableRequestStatuses(),
                ],
                'scenario' => [
                    'type' => Form::INPUT_RAW,
                    'value' => Html::hiddenInput('scenario', 'setState'),
                ],
                'empty2' => [
                    'type' => Form::INPUT_RAW,
                    'value' =>
                        Html::tag(
                            'div',
                            Html::button('Вернуться', [
                                'class' => 'btn btn-link',
                                'style' => 'width: 100px; margin-right: 15px;',
                                'onClick' => 'window.history.back(-1)',
                            ]) .
                            Html::submitButton('Сохранить', [
                                'class' => 'btn btn-primary',
                                'style' => 'width: 100px;',
                            ]),
                            [
                                'style' => 'padding-top: 20px; text-align: right;',
                            ]
                        )
                ]
            ],
        ]);

        ActiveForm::end();
        ?>
    <?php endif; ?>
</div>

<script type="text/javascript">
    jQuery(document).ready(function() {
        $('.switchEditable')
            .on('click', function(e) {
                e.preventDefault();
                var target = $($(this).data('edit')),
                    source = target.next('span');
                if (target.length && source) {
                    target.toggle();
                    source.toggle();
                }
            });
    });
</script>

<script type="text/javascript" src="/js/jquery.nailthumb.min.js"></script>
<script type="text/javascript" src="/js/jquery.multifile.min.js"></script>
<script type="text/javascript" src="/js/behaviors/media-manager.js"></script>