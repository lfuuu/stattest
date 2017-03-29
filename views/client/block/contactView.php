<?php
/**
 * Просмотр контактов
 *
 * @var \app\classes\BaseView $this
 * @var ClientAccount $account
 * @var ClientContact[] $contacts
 */

use app\classes\Html;
use app\models\ClientAccount;
use app\models\ClientContact;
?>

<div id="contacts-view" class="well">

    <div class="row">

        <div class="col-sm-3">
            <h2><a id="contacts-edit-link" class="pointer" title="Редактировать контакты"><img class="icon" src="/images/icons/edit.gif"> Контакты</a></h2>
        </div>

        <?php foreach ($contacts as $contact): ?>
            <div class="col-sm-3 <?= $contact->is_official ? 'bold' : '' ?> <?= $contact->is_validate ? '' : 'bg-danger' ?>">
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

<?= $this->render('contactEdit', ['account' => $account, 'contacts' => $contacts]); ?>