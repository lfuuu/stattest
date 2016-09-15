<?php

/** @var \app\models\Person $person */
/** @var \kartik\widgets\ActiveForm $form */
/** @var string $lang */
?>

<div class="row">
    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form
                ->field($person, 'name_nominative[' . $lang . ']')
                ->textInput(['value' => $person->name_nominative]);
            ?>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form
                ->field($person, 'name_genitive[' . $lang . ']')
                ->textInput(['value' => $person->name_genitive]);
            ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form
                ->field($person, 'post_nominative[' . $lang . ']')
                ->textInput(['value' => $person->post_nominative]);
            ?>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form
                ->field($person, 'post_genitive[' . $lang . ']')
                ->textInput(['value' => $person->post_genitive]);
            ?>
        </div>
    </div>
</div>