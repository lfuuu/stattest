<?php
use \yii\helpers\Html;
?>
<h2>Дополнительные ИНН</h2>

<div class="row">
    <div class="col-sm-2"><?= $model->getAttributeLabel('inn') ?></div>
    <div class="col-sm-2"><?= $model->getAttributeLabel('comment') ?></div>
    <div class="col-sm-2"><?= $model->getAttributeLabel('user_id') ?></div>
    <div class="col-sm-2"><?= $model->getAttributeLabel('ts') ?></div>
    <div class="col-sm-1"></div>
</div>
<?php foreach($account->additionalInn as $inn) : ?>
    <div class="row">
        <div class="col-sm-2"><?= $inn->inn ?></div>
        <div class="col-sm-2"><?= $inn->comment ?></div>
        <div class="col-sm-2"><?= $inn->user->name ?></div>
        <div class="col-sm-2"><?= $inn->ts ?></div>
        <div class="col-sm-1">
            <a href="/account/additional-inn-delete?id=<?= $inn->id ?>">
                <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/delete.gif" alt="Активность">
            </a>
        </div>
    </div>
<?php endforeach; ?>
<div class="row">
    <form action="/account/additional-inn-create?accountId=<?= $account->id ?>" method="post">
        <div class="col-sm-2">
            <?= Html::activeTextInput($model, 'inn', ['class' => 'form-control']) ?>
        </div>
        <div class="col-sm-2">
            <?= Html::activeTextInput($model, 'comment', ['class' => 'form-control has-error']) ?>
        </div>
        <div class="col-sm-3"></div>
        <div class="col-sm-2"><button type="submit" class="btn btn-primary col-sm-12">Добавить</button> </div>
    </form>
</div>
