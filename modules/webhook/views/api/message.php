<?php
/**
 * Всплывашка
 *
 * @var \app\classes\BaseView $this
 * @var string $did
 * @var string $abon
 * @var bool $clientContactsIsOrigin
 * @var ClientContact[] $clientContacts
 */

use app\classes\Html;
use app\models\ClientContact;

?>

<div class="notify_popup_message" id="message_id_<?= $messageId ?>"
<span>
    <?= $did . (isset($calling_did) && $calling_did ? ' (' . $calling_did . ') ' : '') ?> -&gt; <?= $abon ?>
</span>

<?php if ($clientContacts) : ?>
    <table class="table table-condensed" id="client_info_<?= $messageId ?>">
        <?php
        $i = 0;
        foreach ($clientContacts as $clientContact) :
            $clientAccount = $clientContact->client;
            if (!$clientAccount) {
                continue;
            }

            if ($i++ > 12) {
                break; // слишком много все равно не вместится
            }

            $contract = $clientAccount->contract;
            $contragent = $contract->contragent;
            $super = $contract->super;
            $accountManager = $clientAccount->getUserAccountManager();
            ?>
            <tr data-client_account_id="<?= $clientAccount->id ?>">
                <?php if ($clientContactsIsOrigin) : ?>
                    <td><?= Html::encode($clientContact->comment) ?></td>
                <?php endif ?>
                <td><?= Html::a($clientAccount->getAccountType() . ' ' . $clientAccount->id, $clientAccount->getUrl()) ?></td>
                <td>Дог. <?= $contract->number ?></td>
                <td><?= Html::encode($contragent->name) ?></td>
                <td><?= ($contragent->name != $super->name) ? Html::encode($super->name) : '' ?></td>
                <td>Ак. менеджер: <?= $accountManager->name; ?></td>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif ?>
<?= isset($block['down']) ? $block['down'] : '' ?>
</div>
