<?php
use \yii\helpers\Html;
?>
<h2>Дополнительные Р/С</h2>

<div class="row">
    <div class="col-sm-2"><?= $model->getAttributeLabel('pay_acc') ?></div>
    <div class="col-sm-2"><?= $model->getAttributeLabel('who') ?></div>
    <div class="col-sm-2"><?= $model->getAttributeLabel('date') ?></div>
    <div class="col-sm-1"></div>
</div>
<?php foreach($account->additionalPayAcc as $payAcc) : ?>
    <div class="row">
        <div class="col-sm-2"><?= $payAcc->pay_acc ?></div>
        <div class="col-sm-2"><?= $payAcc->user->name ?></div>
        <div class="col-sm-2"><?= $payAcc->date ?></div>
        <div class="col-sm-1">
            <a href="/account/additional-pay-acc-delete?id=<?= $payAcc->id ?>">
                <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/delete.gif" alt="Активность">
            </a>
        </div>
    </div>
<?php endforeach; ?>
<div class="row">
    <form action="/account/additional-pay-acc-create?accountId=<?= $account->id ?>" method="post">
        <div class="col-sm-2">
            <?= Html::activeTextInput($model, 'pay_acc', ['class' => 'form-control']) ?>
        </div>
        <div class="col-sm-3"></div>
        <div class="col-sm-2"><button type="submit" class="btn btn-primary col-sm-12">Добавить</button> </div>
    </form>
</div>
