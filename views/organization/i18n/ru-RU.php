<?php

/** @var \app\models\Organization $organization */
/** @var \kartik\widgets\ActiveForm $form */
/** @var string $lang */

$organization = $organization->setLanguage($lang);
?>

<div class="row">
    <div class="col-sm-6">
        <?= $form
            ->field($organization, 'name[' . $lang . ']')
            ->textInput(['value' => $organization->name])
            ->label('Краткое название')
        ?>
    </div>

    <div class="col-sm-6" style="padding-left: 30px;">
        <?= $form
            ->field($organization, 'legal_address[' . $lang . ']')
            ->textInput(['value' => $organization->legal_address])
            ->label('Юридический адрес')
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <?= $form
            ->field($organization, 'full_name[' . $lang . ']')
            ->textInput(['value' => $organization->full_name])
            ->label('Полное название')
        ?>
    </div>
    <div class="col-sm-6" style="padding-left: 30px;">
        <?= $form
            ->field($organization, 'post_address[' . $lang . ']')
            ->textInput(['value' => $organization->post_address])
            ->label('Почтовый адрес')
        ?>
    </div>
</div>