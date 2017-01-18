<?php

use app\classes\Html;

/** @var \app\models\important_events\ImportantEventsNames $data */
/** @var \kartik\widgets\ActiveForm $form */
?>

<div class="col-sm-12">
    <div class="col-sm-7">
        <?=  ?>
    </div>
    <div class="col-sm-3">
        <?= $this->render('//layouts/_button',
            [
                'text' => 'Опубликовать',
                'params' => [
                    'class' => 'btn btn-success',
                ],
            ])
        ?>
    </div>
    <div class="col-sm-2">
        <div class="btn-group" data-toggle="buttons" aria-required="true">
            <label class="btn btn-default active"><input type="radio" name="Demo[weekdays1]" value="0" checked="checked" /> On</label>
            <label class="btn btn-default"><input type="radio" name="Demo[weekdays1]" value="1"  /> Off</label>
        </div>
    </div>
</div>


