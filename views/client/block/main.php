<?php
use \yii\helpers\Url;
use \app\models\ClientContract;

?>
<div class="main-client">

    <div class="row">
        <div class="col-sm-5">
            <a href="#"><h1 class="c-blue-color"><?= $sClient->name ?></h1></a>
        </div>
        <div class="col-sm-5" class="c-blue-color">
            <a href="#">Переход в личный кабинет</a>
        </div>
        <div class="col-sm-2" class="c-blue-color">
            <a href="<?= Url::toRoute(['contragent/create', 'parentId' => $sClient->id, 'childId' => $activeClient->id]) ?>"><span
                    class="c-blue-color"><i
                        class="glyphicon glyphicon-plus"></i> Новый контрагент</span></a>
        </div>
    </div>
    <?php $contragents = $sClient->contragents;
    foreach ($contragents as $contragent): ?>
        <div class="row" style="padding-top: 10px; border-top: solid #43657d 1px;">
            <div class="col-sm-5">
                <a href="<?= Url::toRoute(['contragent/edit', 'id' => $contragent->id, 'childId' => $activeClient->id]) ?>"><span
                        style="font-size: 18px;" class="c-blue-color"><?= $contragent->name_full ?></span></a>
            </div>
            <div class="col-sm-5">
                <span><?= $contragent->address_jur ? $contragent->address_jur : '...' ?></span>
            </div>
            <div class="col-sm-2">
                <a href="<?= Url::toRoute(['contract/create', 'parentId' => $contragent->id, 'childId' => $activeClient->id]) ?>"><span
                        class="c-blue-color"><i class="glyphicon glyphicon-plus"></i> Новый договор</span></a>
            </div>
        </div>
        <?php $contracts = $contragent->contracts;
        foreach ($contracts as $contract): ?>
            <div class="row">
                <div class="col-sm-1"></div>
                <div class="col-sm-3">
                    <a href="<?= Url::toRoute(['contract/edit', 'id' => $contract->id, 'childId' => $activeClient->id]) ?>"><span
                            class="c-blue-color">№ <?= $contract->number ?> (<?= $contract->organizationName ?>)</span></a>
                </div>
                <div class="col-sm-1" style="padding:0;">
                    <span>
                        <i class="<?= $contract->state == 'unchecked' ? 'uncheck' : 'check' ?>"></i>
                        <?php $states = ClientContract::$states; echo $states[$contract->state]; ?>
                    </span>
                </div>
                <div class="col-sm-3">
                    <?php $bps = $contract->businessProcessStatus; ?>
                    <span><?= $contract->businessProcess ?></span>&nbsp;/&nbsp;<b
                        style="background:<?= $bps['color'] ?>;"><?= $bps['name'] ?></b>
                </div>
                <div class="col-sm-2" style="text-align: right;">
                    <?php if ($contract->managerName) : ?>
                        <span style="background: <?= $contract->managerColor ?>">
                            <i class="glyphicon glyphicon-user i-manager"></i> <?= $contract->managerName ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="col-sm-2">
                    <?php if ($contract->accountManagerName) : ?>
                        <span style="background: <?= $contract->accountManagerColor ?>">
                            <i class="glyphicon glyphicon-user i-accmanager"></i> <?= $contract->accountManagerName ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php /*<div class="col-sm-2">
                    <a href="<?= Url::toRoute(['client/create', 'parentId' => $contract->id, 'childId' => $activeClient->id]) ?>"><span
                            class="c-blue-color"><i class="glyphicon glyphicon-plus"></i> Новый ЛС</span></a>
                </div>*/ ?>
            </div>
            <div>
                <div class="col-sm-11 col-xs-offset-1">
                    <?php foreach ($contract->comments as $comment)
                        if ($comment->is_publish): ?>
                            <div class="col-sm-12">
                                <b><?= $comment->user ?> <?= $comment->ts ?>: </b><?= $comment->comment ?>
                            </div>
                        <?php endif; ?>
                </div>
            </div>
            <?php $clients = $contract->clients;
            foreach ($clients as $client): ?>
                <div class="row" style="margin:5px 0; cursor: pointer;"
                     onclick="location.href='/client/clientview?id=<?= $client->id ?>'">
                    <div class="col-sm-2"></div>
                    <div
                        class="col-sm-10  <?= (isset($activeClient) && $activeClient->id == $client->id) ? 'active-client' : ''; ?>"
                        style="border: solid rgb(82, 164, 203) 1px; border-radius: 5px;">
                        <span class="col-sm-4"
                              style="font-weight: bold; color:<?= ($client->is_blocked) ? 'red' : 'green' ?>;">ЛС № <?= $client->id ?></span>
                        <span class="col-sm-4"><?= $client->regionName ?></span>
                        <span class="col-sm-2"
                              style="text-align: right;color:<?= ($client->balance < 0) ? 'red' : 'green'; ?>;"><?= $client->balance ?>
                            RUB</span>
                        <span class="col-sm-2">(Кредит: <?= $client->credit ?>)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>