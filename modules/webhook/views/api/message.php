<?php
/**
 * Всплывашка
 *
 * @var \app\classes\BaseView $this
 * @var string $did
 * @var string $abon
 * @var ClientContact[] $clientContacts
 */

use app\classes\Html;
use app\models\ClientContact;

?>
    <span>
        <?= $did ?> -&gt; <?= $abon ?>
    </span>

<?php if ($clientContacts) : ?>
    <table class="table table-striped table-condensed">
        <?php
        $i = 0;
        foreach ($clientContacts as $clientContact) :
            $clientAccount = $clientContact->client;
            if (!$clientAccount) {
                continue;
            }

            if ($i++ > 12) {
                // слишком много все равно не вместится
                break;
            }

            $contract = $clientAccount->contract;
            $contragent = $contract->contragent;
            $super = $contract->super;
            ?>
            <tr>
                <td><?= Html::encode($clientContact->comment) ?></td>
                <td><?= Html::a($clientAccount->getAccountType() . ' ' . $clientAccount->id, $clientAccount->getUrl()) ?></td>
                <td>Дог. <?= $contract->number ?></td>
                <td><?= Html::encode($contragent->name) ?></td>
                <td><?= ($contragent->name != $super->name) ? Html::encode($super->name) : '' ?></td>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif ?>