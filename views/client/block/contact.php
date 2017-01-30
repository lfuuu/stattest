<?php
/**
 * Просмотр контактов
 *
 * @var \yii\web\View $this
 * @var ClientAccount $account
 * @var ClientContact[] $contacts
 */

use app\classes\Html;
use app\models\ClientAccount;
use app\models\ClientContact;
use yii\helpers\Url;

?>

<br/>
<div class="well contacts-view">

    <h2><a href="<?= Url::toRoute(['contact/edit', 'id' => $account->id]) ?>" title="Редактировать контакты"><img class="icon" src="/images/icons/edit.gif"> Контакты</a></h2>

    <table class="table table-striped">
        <?php foreach ($contacts as $contact): ?>
            <?php
            if (!$contact->is_active) {
                continue;
            }
            ?>
            <tr class="<?= $contact->is_official ? 'bold' : '' ?> <?= $contact->is_validate ? '' : 'danger' ?>">
                <td><?= ClientContact::$types[$contact->type] ?></td>
                <td>
                    <?php
                    if ($contact->isEmail()) {
                        printf(
                            '%s (%s)',
                            Html::a($contact->data, 'http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&trunk=5&to=' . $contact->data),
                            Html::a('@', 'mailto:' . $contact->data)
                        );
                    } else {
                        echo $contact->data;
                    }
                    ?>
                </td>
                <td><?= $contact->comment ?></td>
            </tr>
        <?php endforeach ?>
    </table>
</div>
