<?php

use app\classes\api\ApiCore;
use app\classes\DateTimeWithUserTimezone;
use app\classes\Html;
use app\helpers\DateTimeZoneHelper;
use app\models\billing\LocksLog;
use app\models\ClientAccount;
use app\models\ClientContract;
use app\models\ClientSuper;
use app\models\EventQueueIndicator;
use yii\helpers\Url;

/** @var ClientSuper $client */
/** @var \app\models\ClientAccount $account */
?>

<div class="main-client">

    <div class="row">
        <div class="col-sm-6">
            <h2 class="c-blue-color" style="margin:0;"><a href="/account/super-client-edit?id=<?= $client->id ?>&childId=<?= $account->id ?>"><?= $client->name ?></a></h2>
        </div>
        <div class="col-sm-4" class="c-blue-color">
            <?php if (ApiCore::isAvailable()) : ?>
                <?php if ($client->isShowLkLink()) :?>
                    <a href="https://<?= Yii::$app->params['CORE_SERVER']; ?>/core/support/login_under_core_admin?stat_client_id=<?= $client->id ?>" target="_blank">
                        Переход в ЛК
                    </a>
                <?php elseif ($indicator = EventQueueIndicator::findOne(['object' => ClientSuper::tableName(), 'object_id' => $account->super_id])) : ?>
                    <?= $this->render('//layouts/_eventIndicator', ['indicator' => $indicator])?>
                <?php elseif ($adminEmails = $client->getAdminEmails()) : ?>
                    <?= $this->render("add_admin_email", ['emails' => $adminEmails, 'account' => $account])?>
                <?php endif; ?>
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
                <div class="row contragent-wrap" id="contragent<?= $contragent->id ?>"
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
                        foreach ($contracts as $contract):
                            ?>
                            <div class="row contract-block" style="margin-left: 0px;">
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
                                    <?php /*/&nbsp;<?= $contract->businessProcess ?></span>&nbsp;*/
                                    ?>
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
                                    <?php foreach ($contract->comments as $comment) {
                                        if ($comment->is_publish) : ?>
                                            <div class="col-sm-12">
                                                <b><?= $comment->user ?> <?= DateTimeZoneHelper::getDateTime($comment->ts) ?>
                                                    : </b><?= $comment->comment ?>
                                            </div>
                                        <?php endif;
                                    } ?>
                                </div>
                                <div class="col-sm-12">
                                    <?php foreach ($contract->accounts as $ck => $contractAccount): ?>
                                        <?php
                                        $warnings = $contractAccount->voipWarnings;
                                        $contractBlockers = [];

                                        $lockByCredit = isset($warnings[ClientAccount::WARNING_CREDIT]) || isset($warnings[ClientAccount::WARNING_FINANCE]);
                                        $lockByDayLimit = isset($warnings[ClientAccount::WARNING_LIMIT_DAY]);
                                        ?>
                                        <div style="position: relative; float: left; top: 5px; left: 5px;<?= ($ck) ? 'margin-top: 10px;' : '' ?>">
                                            <a href="/account/edit?id=<?= $contractAccount->id ?>"><img src="/images/icons/edit.gif"></a>
                                        </div>
                                        <div
                                                style="position: relative;<?= ($ck) ? 'margin-top: 10px;' : '' ?>"
                                                onclick="location.href='/client/view?id=<?= $contractAccount->id ?>'"
                                                class="row row-ls  <?= ($account && $account->id == $contractAccount->id) ? ($account->getContract()->getOrganization()->vat_rate == 0 ? 'active-client-mcm' : 'active-client') : ''; ?>">
                                            <span class="col-sm-2" style="font-weight: bold; color:<?= ($contractAccount->is_active) ? 'green' : 'black' ?>;">
                                                <?= $contractAccount->getAccountTypeAndId() ?>
                                            </span>
                                            <span class="col-sm-2" style="font-weight: bold; color:red;">
                                                <?php
                                                $lastLock = false;

                                                if ($contractAccount->is_blocked) {
                                                    $lastLock = LocksLog::find()
                                                        ->where(['client_id' => $account->id, 'is_blocked' => true])
                                                        ->orderBy(['dt' => SORT_DESC])
                                                        ->one();

                                                    $contractBlockers[] = 'Заблокирован' .
                                                        ($lastLock ?
                                                            ': ' . (new DateTimeWithUserTimezone($lastLock->dt, $account->timezone))->format('H:i:s d.m.Y') :
                                                            ''
                                                        );
                                                }

                                                if (isset($warnings[ClientAccount::WARNING_OVERRAN])) {
                                                    $lastLock = $warnings[ClientAccount::WARNING_OVERRAN];

                                                    $contractBlockers[] = Html::tag('abbr',
                                                        'Блок превышение' .
                                                        (
                                                        $lastLock ?
                                                            ': ' . (new DateTimeWithUserTimezone($lastLock->dt, $account->timezone))->format('H:i:s d.m.Y') :
                                                            ''
                                                        ),
                                                        [
                                                            'title' => 'ЛС заблокирован по превышению лимитов. Возможно, его взломали'
                                                        ]
                                                    );
                                                }

                                                if (isset($warnings[ClientAccount::WARNING_MN_OVERRAN])) {
                                                    $lastLock = $warnings[ClientAccount::WARNING_MN_OVERRAN];

                                                    $contractBlockers[] = Html::tag('abbr',
                                                        'Блок превышение МН' .
                                                        (
                                                        $lastLock ?
                                                            ': ' . (new DateTimeWithUserTimezone($lastLock->dt, $account->timezone))->format('H:i:s d.m.Y') :
                                                            ''
                                                        ),
                                                        [
                                                            'title' => 'ЛС заблокирован по превышению лимитов (МН). Возможно, его взломали'
                                                        ]
                                                    );
                                                }

                                                if ($lockByDayLimit) {
                                                    $contractBlockers[] = Html::tag('abbr', 'Блок лимит', ['title' => 'ЛС заблокирован по превышению дневного лимита']);
                                                }

                                                if ($lockByCredit) {
                                                    $contractBlockers[] = Html::tag('abbr', 'Блок фин.', ['title' => 'ЛС находится в финансовой блокировке. Она автоматически снимется после пополнения счета']);
                                                }

                                                echo implode(' / ', $contractBlockers);
                                                ?>
                                            </span>
                                            <span class="col-sm-1" style="text-align: right;">
                                                <?= $contractAccount->regionName ?>
                                            </span>
                                            <span class="col-sm-2 text-right">
                                                <abbr
                                                        title="Текущий баланс лицевого счета"
                                                        class="text-nowrap"
                                                        style="color:<?= ($lockByCredit ? 'red' : 'green'); ?>;"
                                                >
                                                    <?= sprintf('%0.2f', $contractAccount->billingCounters->realtimeBalance) ?>
                                                    <?= $contractAccount->currency ?>
                                                </abbr>
                                                <br/>
                                                <abbr title="Размер кредита" class="text-nowrap">
                                                    <?= $contractAccount->credit >= 0 ? 'Кредит: ' . $contractAccount->credit : '' ?>
                                                </abbr>
                                            </span>
                                            <span class="col-sm-2 text-right" style="text-align: right;">
                                                <?php if ($contract->business_id == \app\models\Business::OPERATOR) : ?>
                                                    <abbr
                                                            title="Реалтаймовый счетчик стоимости всех входящих звонков по ЛС" class="text-nowrap"
                                                            style="color:<?= ($lockByDayLimit ? 'red' : 'green'); ?>;"
                                                    >
                                                        Ориг.: <?= abs($contractAccount->interopCounter->income_sum); ?> <?= $contractAccount->currency; ?>
                                                    </abbr>
                                                    <br/>
                                                    <abbr
                                                            title="Реалтаймовое счетчик стоимости всех исходящих звонков по ЛС" class="text-nowrap"
                                                            style="color:<?= ($lockByDayLimit ? 'red' : 'green'); ?>;"
                                                    >
                                                        Терм.: <?= abs($contractAccount->interopCounter->outcome_sum); ?> <?= $contractAccount->currency; ?>
                                                    </abbr>

                                                <?php else: ?>
                                                    <abbr
                                                            title="Суточный расход" class="text-nowrap"
                                                            style="color:<?= ($lockByDayLimit ? 'red' : 'green'); ?>;"
                                                    >
                                                        <?= abs($contractAccount->billingCounters->daySummary); ?>
                                                        <?= $contractAccount->currency; ?>
                                                    </abbr>
                                                    <br/>
                                                    <abbr title="Дневной лимит" class="text-nowrap">
                                                        <?php
                                                        echo ($contractAccount->voip_is_day_calc === 1 ? 'Авто лим. ' : 'Сут.лим. ') . ': ';
                                                        echo $contractAccount->voip_credit_limit_day;
                                                        ?>
                                                    </abbr>
                                                <?php endif; ?>
                                            </span>
                                            <div class="btn-group" style="float: right; padding-top: 12px;">
                                                <?php if ($contractAccount->hasVoip) : ?>
                                                    <?php if ($contractAccount->voip_disabled) : ?>
                                                        <button
                                                                type="button" class="btn btn-sm set-voip-disabled <?= $contractAccount->voip_disabled ? 'btn-danger' : 'btn-success' ?>"
                                                                style="width: 120px;padding: 3px 10px;"
                                                                data-id="<?= $contractAccount->id ?>"
                                                                title="<?= $contractAccount->voip_disabled ? 'Выключить локальную блокировку' : 'Включить локальную блокировку' ?>"
                                                        >
                                                            <?= $contractAccount->voip_disabled ? 'Лок. разблок.' : 'Лок. блок.' ?>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <button
                                                        type="button" class="btn btn-sm set-block <?= $contractAccount->is_blocked ? 'btn-danger' : 'btn-success' ?>"
                                                        style="width: 120px;padding: 3px 10px;"
                                                        data-id="<?= $contractAccount->id ?>"
                                                >
                                                    <?= $contractAccount->is_blocked ? 'Разблокировать' : 'Заблокировать' ?>
                                                </button>
                                            </div>
                                            <?php if ($warnings) : ?>
                                                <div class="col-sm-12">
                                                    <?php foreach ($warnings as $warningCode => $warningText): ?>
                                                        <?php if (is_string($warningText)) : ?>
                                                            <span class="label label-danger"><?= $warningText; ?></span>
                                                        <?php endif; ?>
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

        $('.set-voip-disabled').click(function (e) {
            e.stopPropagation();
            var id = $(this).data('id');
            t = $(this);
            if (confirm(t.hasClass('btn-danger') ? 'Выключить локальную блокировку' : 'Включить локальную блокировку')) {
                if (t.hasClass('btn-danger')) {
                    t.addClass('btn-success').removeClass('btn-danger').text('Лок. блок.').attr('title', 'Включить локальную блокировку');
                } else {
                    t.addClass('btn-danger').removeClass('btn-success').text('Лок. разблок.').attr('title', 'Выключить локальную блокировку');
                }

                location.href = '/account/set-voip-disable?id=' + id;
            }
        });
    })
</script>