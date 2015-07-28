<?php
use \yii\helpers\Url;

$contacts = $account->allContacts;
$contactsArr = [];
foreach($contacts as $contact){
    if($contact->data && $contact->is_active)
        $contactsArr[$contact->type][] = $contact;
}

$translate = [
    'email' => 'Email',
    'phone' => 'Тел.',
    'fax' => 'Факсы',
    'sms' => 'СМС',
];

?>

<div class="data-block row data-contacts">
    <form action="<?= Url::toRoute(['contact/create', 'clientId' => $account->id]) ?>" method="post">
        <div class="row">
            <div class="col-sm-2 showFullTable" style="cursor: pointer;">
                <a><img class="icon" src="/images/icons/monitoring.gif" alt="Посмотреть"></a>Контакты
            </div>
            <div class="col-sm-10">
                <?php foreach($contactsArr as $contactType => $contactsInType): ?>
                <div class="row">
                    <div class="col-sm-1"><?= $translate[$contactType] ?></div>
                    <div class="col-sm-11">
                        <?php foreach ($contactsInType as $contact){
                                if ($contactType == 'email'){
                                    echo "<a style=\"".($contact->is_official?'font-weight:bold':'')."\" target=\"_blank\"
                                       href=\"http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&trunk=5&to={$contact->data}\">
                                       {$contact->data}
                                       </a>
                                        <a style=\"".($contact->is_official?'font-weight:bold':'')."\" href=\"mailto:{$contact->data}\">(@)</a>".($contact->comment ? '&nbsp-&nbsp'. $contact->comment : '')."; &nbsp;";
                                } else {
                                    echo $contact->data . ($contact->comment ? '&nbsp-&nbsp'. $contact->comment : '') .";&nbsp";
                                }
                        } ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="col-sm-12 fullTable" style="display: none;">
                <div class="row head3">
                    <div class="col-sm-2"></div>
                    <div class="col-sm-1">Тип</div>
                    <div class="col-sm-2">Контакт</div>
                    <div class="col-sm-2">Комментарий</div>
                    <div class="col-sm-2">Кто добавил</div>
                    <div class="col-sm-2">Когда</div>
                    <div class="col-sm-1"></div>
                </div>
                <?php foreach ($contacts as $contact) : ?>
                    <div class="row"
                         style="<?= ($contact->is_official) ? 'font-weight:bold;' : '' ?><?= ($contact->is_active) ? 'color:black;' : 'color:#909090;' ?>">
                        <div class="col-sm-2">
                            <?php if ($contact->type == 'email' && $contact->is_active) : ?>
                                Администратор ЛК
                                <input type="radio" name="admin-lk-id"
                                       value="<?= $contact->id ?>" <?= ($contact->id == $account->admin_contact_id) ? 'checked' : '' ?> />
                            <?php endif ?>
                        </div>
                        <div class="col-sm-1"><?= $contact->type ?></div>
                        <div class="col-sm-2"><?= $contact->data ?></div>
                        <div class="col-sm-2"><?= $contact->comment ?></div>
                        <div class="col-sm-2"><?= $contact->userUser->name ?></div>
                        <div class="col-sm-2"><?= $contact->ts ?></div>
                        <div class="col-sm-1">
                            <?php if ($contact->userUser == 'AutoLK'): ?>
                                <a href="<?= Url::toRoute(['contact/lk-activate', 'id' => $contact->id]) ?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon"
                                         src="/images/icons/<?= $contact->is_active ? 'action_check_off.gif' : 'action_check.gif' ?>"
                                         alt="Активность">
                                </a>
                            <?php else: ?>
                                <a href="<?= Url::toRoute(['contact/activate', 'id' => $contact->id]) ?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon"
                                         src="/images/icons/<?= $contact->is_active ? 'delete.gif' : 'add.gif' ?>"
                                         alt="Активность">
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="row">
                    <div class="col-sm-2">
                        <input type="radio" name="admin-lk-id" value="1" <?= (0 == $account->admin_contact_id) ? 'checked' : '' ?> />
                        <button type="submit" name="set-admin-lk" class="btn btn-primary">Администратор ЛК</button>
                    </div>
                    <div class="col-sm-2">
                        <select name="type" class="form-control" style="font-size:10px">
                            <option value="phone">телефон</option>
                            <option value="fax">факс</option>
                            <option value="email">e-mail</option>
                            <option value="sms">СМС</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <input class="form-control" type="text" placeholder="Контактные данные" name="data">
                    </div>
                    <div class="col-sm-2">
                        <input class="form-control" type="text" placeholder="Комментарий" name="comment">
                    </div>
                    <input type="hidden" name="user_id" value="<?=Yii::$app->user->id?>">
                    <div class="col-sm-2">
                        <input type="checkbox" name="is_official" value="1" title="официальный?" />
                        Официальный
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary col-sm-12">Добавить</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>