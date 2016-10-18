<?php

use app\classes\Html;
use app\classes\important_events\ImportantEventsDetailsFactory;

/** @var \app\models\important_events\ImportantEvents $model */
?>

<?= ImportantEventsDetailsFactory::get($model->event, $model)->getDescription(); ?>
<br /><br />

<div class="row col-sm-12">
    <div class="col-sm-6"></div>
    <div class="col-sm-6">
        <label class="control-label">Комментарий</label>
        <div class="row">
            <div class="col-sm-10">
                <?= Html::input('text', 'comment', $model->comment, [
                    'class' => 'form-control',
                    'data-important-event-id' => $model->id,
                ]) ?>
            </div>
            <div class="col-sm-2">
                <?= $this->render('//layouts/_button', [
                    'text' => Yii::t('common', 'Save'),
                    'glyphicon' => 'glyphicon-save',
                    'params' => [
                        'class' => 'btn btn-primary important-events-comment-btn',
                        'data-important-event-id' => $model->id,
                    ],
                ])
                ?>
            </div>
        </div>
    </div>
</div>