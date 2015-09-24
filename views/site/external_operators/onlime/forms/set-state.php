<?php

use kartik\widgets\ActiveForm;
use kartik\builder\Form;
use yii\helpers\Html;

/** @var RequestOnlimeStateForm $model */
$form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_VERTICAL,
]);

$billItems = $bill->lines;
$userTimeZone = Yii::$app->user->identity->timezone_name;
$stages = $operator->report->getTroubleStages($trouble->id);
$model->state_id = $trouble->currentStage->state_id;
?>

<div class="well" style="padding-top: 60px;">
    <legend>Просмотр счета №<?= $bill->bill_no; ?></legend>

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
                                $dateStartView = new DateTime($dateStartView, new DateTimeZone($userTimeZone));
                                $dateStartView = $dateStartView->format('Y-m-d');
                            }
                            if (!preg_match('#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#', $dateEndView)) {
                                $dateEndView = new DateTime($dateEndView, new DateTimeZone($userTimeZone));
                                $dateEndView = $dateEndView->format('Y-m-d');
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
                                $dateStartView = new DateTime($dateStartView, new DateTimeZone($userTimeZone));
                                $dateStartView = $dateStartView->format('Y-m-d');
                            }
                            echo $dateCloseView;
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <legend style="font-size: 16px;">Этап</legend>
    <?php
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
            'empty1' => ['type' => Form::INPUT_RAW],
            'empty2' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::tag(
                        'div',
                        Html::button('Вернуться', [
                            'class' => 'btn btn-link',
                            'id' => 'dialog-close',
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
    ?>
</div>

<?php
ActiveForm::end();
?>

<script type="text/javascript">
    jQuery(document).ready(function() {
        $('#dialog-close').click(function() {
            window.parent.location.reload(true);
            window.parent.$dialog.dialog('close');
        });
    });
</script>