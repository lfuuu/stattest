<?php

/** @var \app\forms\client\ContractEditForm $contract */

use app\models\ClientContact;

?>

<div id="dialog-form" title="Отправить файл">
    <div class="col-sm-12">
        <div class="form-group">
            <form method="post" id="send-file-form" target="_blank"
                  action="http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&trunk=5">
                <label for="client-email">Email</label>
                <select id="client-email" class="form-control" name="to">
                    <?php /** @var ClientContact $contact */ ?>
                    <?php foreach ($contract->accounts[0]->allContacts as $contact) : ?>
                        <?php if ($contact->isEmail()) : ?>
                            <option value="<?= $contact->data ?>"><?= $contact->data ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="file_content" id="file_content"/>
                <input type="hidden" name="file_name" id="file_name"/>
                <input type="hidden" name="file_mime" id="file_mime"/>
                <input type="hidden" name="msg_session" id="msg_session"/>
                <input type="hidden" name="send_from_stat" value="1"/>
            </form>
        </div>
    </div>
</div>