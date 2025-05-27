<?php
/**
 * SIM-карты. Список у клиента
 *
 * @var \app\classes\BaseView $this
 * @var \app\models\ClientAccount $account
 */

use app\classes\Html;
use app\modules\sim\models\Card;

$cards = Card::find()
    ->where(['client_account_id' => $account->id])
    ->with(['imsies', 'imsies.number'])
    ->all();

if (!$cards) {
    return;
}
?>

    <div class="col-sm-4">
        <div class="panel panel-danger">
            <div class="panel-heading">
                <h2 class="panel-title">SIM-карты</h2>
            </div>

            <div class="panel-body">
                <?php
                /** @var Card $card */
                foreach ($cards as $card) {
                    echo $card->getLink();

                    $cnt = 0;

                    if (!($imsies = $card->imsies)) {
                        echo ' ';

                        continue;
                    }

                    echo Html::beginTag('small');
                    foreach ($imsies as $imsi) {
                        if ($imsi->msisdn) {
                            if (!$cnt) {
                                echo '&nbsp;(';
                            }

                            if ($cnt) {
                                echo ', ';
                            }
                            $cnt++;

                            echo $imsi->number->link;
                        }
                    }

                    if ($cnt) {
                        echo ')';
                    }
                    echo Html::endTag('small') . ' ';
                }
                ?>
            </div>
        </div>
    </div>
<?php
if ($account->superClient->entry_point_id != \app\models\EntryPoint::ID_MNP_RU_DANYCOM) {
//    return;
}

return;

$isView = true;
ob_start();
?>
    <form action="/sim/card/link">
        <input type="hidden" name="account_id" value="<?= $account->id ?>">
        <div class="col-sm-4">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h2 class="panel-title">SIM-карты. Управление.</h2>
                </div>

                <div class="panel-body">
                    <div class="row">
                        <?php
                        [$cards, $accountTariffs] = \app\modules\sim\classes\Linker::me()->getDataByAccountId($account->id);
                        $imsies = array_keys($cards);
                        $accountTariffIds = array_keys($accountTariffs);

                        ?>
                        <?php if ($cards && $accountTariffs) : ?>
                            <div class="col-sm-4">ICCID:</div>
                            <div class="col-sm-8">
                                <?= \yii\helpers\Html::dropDownList('connect_iccid', $cards ? reset($imsies) : null, $cards, ['class' => 'select2']) ?>
                            </div>
                            <div class="col-sm-4">Номер:</div>
                            <div class="col-sm-8">
                                <?= \yii\helpers\Html::dropDownList('connect_account_tariff_id', $accountTariffs ? reset($accountTariffIds) : null, $accountTariffs, ['class' => 'select2']) ?>
                            </div>
                            <div class="col-sm-6"></div>
                            <div class="col-sm-4">
                                <button type="subimt" name="link_iccid_and_number" class="btn btn-warning">Соединить
                                </button>
                            </div>
                            <div class="col-sm-2"></div>
                        <?php elseif ($cards || $accountTariffs) : ?>
                            <div class="text-danger" style="text-align: center;">Не полные
                                данные!!! <?= count($accountTariffs) ?>/<?= count($cards) ?></div>
                        <?php else : ?>
                            <div class="text-info" style="text-align: center;">Не требуется действий</div>
                            <?php $isView = false; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php
$content = ob_get_clean();

if ($isView) {
    echo $content;
}