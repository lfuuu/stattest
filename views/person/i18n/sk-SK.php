<?php

/** @var \app\models\Person $person */
/** @var \kartik\widgets\ActiveForm $form */
/** @var string $lang */

echo $this->render('en-EN', [
    'form' => $form,
    'person' => $person,
    'lang' => $lang,
]);