<?php
use \yii\helpers\Url;
use \app\models\ClientContract;

?>
<div class="main-client">

    <div class="row">
        <div class="col-sm-8">
            <h2 class="c-blue-color" style="margin:0;"><a href="/account/super-client-edit?id=<?= $client->id ?>&childId=<?=$account->id?>"><?= $client->name ?></a></h2>
        </div>
        <div class="col-sm-2" class="c-blue-color">
            <?php if (isset(Yii::$app->params['CORE_SERVER']) && Yii::$app->params['CORE_SERVER']):?>
            <a href="https://<?= Yii::$app->params['CORE_SERVER'] ?>/core/support/login_under_core_admin?stat_client_id=<?= $client->id ?>" target="_blank">
                Переход в ЛК
            </a>
            <?php endif; ?>
        </div>
        <div class="col-sm-2" class="c-blue-color">
            <a href="<?= Url::toRoute(['contragent/create', 'parentId' => $client->id, 'childId' => $account->id]) ?>">
                <span class="c-blue-color">
                    <i class="glyphicon glyphicon-plus"></i> Новый контрагент
                </span>
            </a>
        </div>
        <div class="col-sm-12">
            <?php $contragents = $client->contragents;
            foreach ($contragents as $k => $contragent): ?>
                <div class="row contragent-wrap" id="contragent<?=$contragent->id?>"
                     style="padding-top: 10px; border-top: solid #43657d 1px;padding-bottom: 10px;">
                    <div class="col-sm-5">
                        <a href="<?= Url::toRoute(['contragent/edit', 'id' => $contragent->id, 'childId' => $account->id]) ?>">
                            <span style="font-size: 18px;" class="c-blue-color"><?= trim($contragent->name) ? $contragent->name : '<i>Не задано</i>' ?></span></a>
                    </div>
                    <div class="col-sm-5" style="  overflow: hidden;white-space: nowrap;text-overflow: ellipsis;">
                        <span><?= $contragent->address_jur ? $contragent->address_jur : '...' ?></span>
                    </div>
                    <div class="col-sm-2">
                        <a href="<?= Url::toRoute(['contract/create', 'parentId' => $contragent->id, 'childId' => $account->id]) ?>">
                            <span class="c-blue-color"><i class="glyphicon glyphicon-plus"></i> Новый договор
                            </span>
                        </a>
                    </div>
                    <div class="col-sm-12">
                        <?php $contracts = $contragent->contracts;
                        foreach ($contracts as $contract): ?>
                            <div class="row" style="margin-left: 0px;">
                                <div class="col-sm-5">
                                    <a href="<?= Url::toRoute(['contract/edit', 'id' => $contract->id, 'childId' => $account->id]) ?>">
                                        <span class="c-blue-color">
                                            Договор № <?= $contract->number ? $contract->number : 'Без номера' ?>
                                            (<?= $contract->organization->name ?>)
                                        </span>
                                        &nbsp;
                                        <span>
                                            <i class="<?= $contract->state == ClientContract::STATE_UNCHECKED ? 'uncheck' : 'check' ?>"></i>
                                            <?php $states = ClientContract::$states;
                                            echo $states[$contract->state]; ?>
                                        </span>
                                    </a>
                                </div>
                                <div class="col-sm-3">
                                    <?php $bps = $contract->businessProcessStatus; ?>
                                    <span><?= $contract->business ?></span>&nbsp;
                                    <?php /*/&nbsp;<?= $contract->businessProcess ?></span>&nbsp;*/?>
                                    /&nbsp;<b style="background:<?= isset($bps['color']) ? $bps['color'] : '' ?>;"><?= isset($bps['name']) ? $bps['name'] : '' ?></b>
                                </div>
                                <div class="col-sm-4">
                                    <?php if ($contract->managerName) : ?>
                                        <span style="float:left;background: <?= $contract->managerColor ?>;">
                            М: <?= $contract->managerName ?>
                        </span>
                                    <?php endif; ?>
                                    <?php if ($contract->accountManagerName) : ?>
                                        <span style="float:right;background: <?= $contract->accountManagerColor ?>;">
                            Ак.М: <?= $contract->accountManagerName ?>
                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-sm-12">
                                    <?php foreach ($contract->comments as $comment)
                                        if ($comment->is_publish): ?>
                                            <div class="col-sm-12">
                                                <b><?= $comment->user ?> <?= $comment->ts ?>
                                                    : </b><?= $comment->comment ?>
                                            </div>
                                        <?php endif; ?>
                                </div>
                                <div class="col-sm-12">
                                    <?php foreach ($contract->accounts as $ck => $contractAccount): ?>
                                        <?php
                                        $realtimeBalance = $contractAccount->getRealtimeBalance();
                                        ?>
                                        <a href="/account/edit?id=<?= $contractAccount->id ?>" style="position: absolute;top: 4px; left: 20px;"><img src="/images/icons/edit.gif"></a>
                                        <div
                                            style="<?= ($ck) ? 'margin-top: 10px;' : '' ?>"
                                            onclick="location.href='/client/view?id=<?= $contractAccount->id ?>'"
                                            class="row row-ls  <?= ($account && $account->id == $contractAccount->id) ? ($account->getContract()->getOrganization()->vat_rate == 0 ? 'active-client-mcm' : 'active-client') : ''; ?>">
                                            <span class="col-sm-2"
                                                  style="font-weight: bold; color:<?= ($contractAccount->is_active) ? 'green' : 'black' ?>;">
                                                ЛС № <?= $contractAccount->id ?>
                                            </span>
                                            <span class="col-sm-2" style="font-weight: bold; color:red;">
                                                <?= $contractAccount->is_blocked ? 'Заблокирован' : '' ?>
                                            </span>
                                            <span class="col-sm-2" style="text-align: right;">
                                                <?= $contractAccount->regionName ?>
                                            </span>
                                            <span class="col-sm-2"
                                                  style="text-align: right;color:<?= ($realtimeBalance < 0) ? 'red' : 'green'; ?>;">
                                                <?= $realtimeBalance ?>
                                                <?= $contractAccount->currency ?>
                                            </span>
                                            <span class="col-sm-2">
                                                <?= $contractAccount->credit >= 0 ? '(Кредит: ' . $contractAccount->credit . ')': '' ?>
                                            </span>
                                            <button type="button" class="btn btn-sm set-block
                                            <?= $contractAccount->is_blocked ? 'btn-danger' : 'btn-success' ?>"
                                                    style="width: 120px;float: right;padding: 3px 10px;"
                                                    data-id="<?= $contractAccount->id ?>">
                                                <?= $contractAccount->is_blocked ? 'Разблокировать' : 'Заблокировать' ?>
                                            </button>
                                            <?php
                                            if ($account && $account->id == $contractAccount->id && $voipWarnings = $account->getVoipWarnings()): ?>
                                                <div class="col-sm-12">
                                                    <?php foreach($voipWarnings as $warning): ?>
                                                        <span class="label label-danger"><?=$warning?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
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
    $(function () {

        $('.active-client').closest('.contragent-wrap').addClass('active-contragent');
        $('.active-client-mcm').closest('.contragent-wrap').addClass('active-contragent-mcm');

        $('.set-block').click(function (e) {
            e.stopPropagation();
            var id = $(this).data('id');
            t = $(this);
            if (confirm('Вы уверены, что хотите ' + t.text().toLowerCase().trim() + ' ЛС № ' + id + '?')) {
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