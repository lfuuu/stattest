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
                ->textInput(['value' => $person->setLanguage($lang)->name_nominative]);
            ?>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="col-sm-12">
            <?= $form
                ->field($person, 'post_nominative[' . $lang . ']')
                ->textInput(['value' => $person->setLanguage($lang)->post_nominative]);
            ?>
        </div>
    </div>
</div>