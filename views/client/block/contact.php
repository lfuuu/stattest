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

<div id="contacts-view" class="well">

    <div class="row">

        <div class="col-sm-3">
            <h2><a href="<?= Url::toRoute(['contact/edit', 'id' => $account->id]) ?>" title="Редактировать контакты"><img class="icon" src="/images/icons/edit.gif"> Контакты</a></h2>
        </div>

        <?php foreach ($contacts as $contact): ?>
            <?php
            if (!$contact->is_active) {
                continue;
            }

            ?>
            <div class="col-sm-3 <?= $contact->is_official ? 'bold' : '' ?> <?= $contact->is_validate ? '' : 'danger' ?>">
                <div class="contacts-view-data">
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
                </div>
                <div class="contacts-view-comment">
                    <?= $contact->comment ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>

<style>
    #contacts-view {
        margin: 10px 0 0 0;
        padding: 10px 10px 0 10px;
    }

    #contacts-view h2 {
        margin: 0;
    }

    .contacts-view-comment {
        font-weight: normal;
        margin-bottom: 10px;
        color: #888;
    }
</style>