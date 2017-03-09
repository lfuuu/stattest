<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var \app\models\ClientAccount $account */
/** @var array $emails */
?>
<div class="well" style="width: 300px;">
    <label>
        Создание администратора в ЛК
    </label>
    <form action="<?= Url::to(['/client/add-admin-lk', 'id' => $account->id])?>">
        <div class="form-inline form-group form-group-sm">
            <?= Html::hiddenInput('account_id', $account->id) ?>
            <?= Html::dropDownList('admin_email_id', null, $emails, ['class' => 'form-control']) ?>
            <?= Html::submitButton('Создать', ['class' => 'btn btn-sm btn-primary', 'id' => 'add_admin']) ?>
        </div>
    </form>
</div>
