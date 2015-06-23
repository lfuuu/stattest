<?php
use \yii\helpers\Url;
use \app\models\ClientContract;

?>
<div class="main-client">

    <div class="row">
        <div class="col-sm-5">
            <h2 class="c-blue-color" style="margin:0;"><a href="#"><?= $sClient->name ?></a></h2>
        </div>
        <div class="col-sm-5" class="c-blue-color">
            <?php/*<a href="#">Переход в личный кабинет</a>*/?>
        </div>
        <div class="col-sm-2" class="c-blue-color">
            <a href="<?= Url::toRoute(['contragent/create', 'parentId' => $sClient->id, 'childId' => $activeClient->id]) ?>"><span
                    class="c-blue-color"><i
                        class="glyphicon glyphicon-plus"></i> Новый контрагент</span></a>
        </div>
        <div class="col-sm-12">
            <?php $contragents = $sClient->contragents;
            foreach ($contragents as $k => $contragent): ?>
                <div class="row contragent-wrap" style="padding-top: 10px; border-top: solid #43657d 1px;padding-bottom: 10px;">
                    <div class="col-sm-5">
                        <a href="<?= Url::toRoute(['contragent/edit', 'id' => $contragent->id, 'childId' => $activeClient->id]) ?>">
                            <span style="font-size: 18px;" class="c-blue-color"><?= $contragent->name_full ?></span></a>
                    </div>
                    <div class="col-sm-5">
                        <span><?= $contragent->address_jur ? $contragent->address_jur : '...' ?></span>
                    </div>
                    <div class="col-sm-2">
                        <a href="<?= Url::toRoute(['contract/create', 'parentId' => $contragent->id, 'childId' => $activeClient->id]) ?>"><span
                                class="c-blue-color"><i class="glyphicon glyphicon-plus"></i> Новый договор</span></a>
                    </div>
                    <div class="col-sm-12">
                        <?php $contracts = $contragent->contracts;
                        foreach ($contracts as $contract): ?>
                            <div class="row" style="margin-left: 0px;">
                                <div class="col-sm-5">
                                    <a href="<?= Url::toRoute(['contract/edit', 'id' => $contract->id, 'childId' => $activeClient->id]) ?>">
                                        <span class="c-blue-color">Договор № <?= $contract->number ?> (<?= $contract->organizationName ?>)</span>
                                    </a>
                                </div>
                                <div class="col-sm-1" style="padding:0;">
                    <span>
                        <i class="<?= $contract->state == 'unchecked' ? 'uncheck' : 'check' ?>"></i>
                        <?php $states = ClientContract::$states;
                        echo $states[$contract->state]; ?>
                    </span>
                                </div>
                                <div class="col-sm-3">
                                    <?php $bps = $contract->businessProcessStatus; ?>
                                    <span><?= $contract->businessProcess ?></span>&nbsp;/&nbsp;<b
                                        style="background:<?= $bps['color'] ?>;"><?= $bps['name'] ?></b>
                                </div>
                                <div class="col-sm-3" style="text-align: right;">
                                    <?php if ($contract->managerName) : ?>
                                        <span style="background: <?= $contract->managerColor ?>">
                            <i class="glyphicon glyphicon-user i-manager"></i> <?= $contract->managerName ?>
                        </span>
                                    <?php endif; ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <?php if ($contract->accountManagerName) : ?>
                                        <span style="background: <?= $contract->accountManagerColor ?>">
                            <i class="glyphicon glyphicon-user i-accmanager"></i> <?= $contract->accountManagerName ?>
                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-sm-12">
                                    <?php foreach ($contract->comments as $comment)
                                        if ($comment->is_publish): ?>
                                            <div class="col-sm-12">
                                                <b><?= $comment->user ?> <?= $comment->ts ?>: </b><?= $comment->comment ?>
                                            </div>
                                        <?php endif; ?>
                                </div>
                                <div class="col-sm-12">
                                    <?php $clients = $contract->clients;
                                    foreach ($clients as $client): ?>
                                        <div style="border: solid rgb(82, 164, 203) 1px; border-radius: 5px; margin-left: 30px; cursor: pointer;"
                                             onclick="location.href='/client/view?id=<?= $client->id ?>'"
                                             class="row  <?= (isset($activeClient) && $activeClient->id == $client->id) ? 'active-client' : ''; ?>">
                        <span class="col-sm-2"
                              style="font-weight: bold; color:<?= ($client->is_active) ? 'green' : 'black' ?>;">ЛС № <?= $client->id ?></span>
                        <span class="col-sm-2" style="font-weight: bold; color:red;"><?= $client->is_blocked ? 'Заблокирован' : ''?></span>
                                            <span class="col-sm-2" style="text-align: right;"><?= $client->regionName ?></span>
                        <span class="col-sm-2"
                              style="text-align: right;color:<?= ($client->balance < 0) ? 'red' : 'green'; ?>;"><?= $client->balance ?>
                            RUB</span>
                                            <span class="col-sm-2">(Кредит: <?= $client->credit > 0 ? $client->credit : '0' ?>)</span>
                                            <button type="button" class="btn btn-sm set-block
                                            <?= $client->is_blocked ? 'btn-danger':'btn-success' ?>"
                                                    style="width: 120px;float: right;padding: 3px 10px;" data-id="<?= $client->id ?>"><?= $client->is_blocked ? 'Разблокировать':'Заблокировать' ?></button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    $(function(){

        $('.active-client').closest('.contragent-wrap').addClass('active-contragent');

        $('.set-block').click(function(e) {
            e.stopPropagation();
            var id = $(this).data('id');
            t = $(this);
            if(confirm('Вы уверены, что хотите ' + t.text().toLowerCase() + ' ЛС № ' + id +'?')) {
                if (t.hasClass('btn-danger')) {
                    t.addClass('btn-success').removeClass('btn-danger').text('Заблокировать');
                } else {
                    t.addClass('btn-danger').removeClass('btn-success').text('Разблокировать');
                }

                location.href = '/account/set-block?id=' + id;
            }
        });
    })
</script>