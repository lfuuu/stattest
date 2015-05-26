<?php
use \yii\helpers\Url;
?>
<?php $contacts = $client->allContacts; ?>

<div class="data-block">
    <div class="row">
        <div class="col-sm-2 showFullTable" style="cursor: pointer;">
            <a><img class="icon" src="/images/icons/monitoring.gif" alt="Посмотреть"></a>Контакты
        </div>
        <div class="col-sm-10">
            <div class="row">
                <div class="col-sm-1">Телефоны</div>
                <div class="col-sm-11">
                    <?php foreach ($contacts as $contact)
                        if (($contact->type == 'phone' || $contact->type == 'sms' || $contact->type == 'fax') && $contact->is_active): ?>
                            <?= $contact->data ?>(<?= $contact->type ?>);&nbsp;
                        <?php endif; ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-1">Email</div>
                <div class="col-sm-11">
                    <?php foreach ($contacts as $contact)
                        if ($contact->type == 'email' && $contact->is_active): ?>
                            <a style="font-weight:bold" target="_blank"
                               href="http://thiamis.mcn.ru/welltime/?module=com_agent_panel&frame=new_msg&nav=mail.none.none&message=none&trunk=5&to=<?= $contact->data ?>"
                               title=""><?= $contact->data ?></a>
                            <a style="font-weight:bold" href="mailto:<?= $contact->data ?>">(@)</a>; &nbsp;
                        <?php endif; ?>
                </div>
            </div>

            <div class="fullTable row" style="display: none;">
                <div class="col-sm-12 head3">
                    <div class="col-sm-2">Тип</div>
                    <div class="col-sm-2">Контакт</div>
                    <div class="col-sm-2">Комментарий</div>
                    <div class="col-sm-2">Кто добавил</div>
                    <div class="col-sm-2">Когда</div>
                    <div class="col-sm-2"></div>
                </div>
                <?php foreach ($contacts as $contact) : ?>
                    <div class="col-sm-12">
                        <div class="col-sm-2"><?= $contact->type ?></div>
                        <div class="col-sm-2"><?= $contact->data ?></div>
                        <div class="col-sm-2"><?= $contact->comment ?></div>
                        <div class="col-sm-2"><?= $contact->userName ?></div>
                        <div class="col-sm-2"><?= $contact->ts ?></div>
                        <div class="col-sm-2">
                            <?php if ($contact->userUser == 'AutoLK'): ?>
                                <a href="<?= Url::toRoute(['contact/lkactivate', 'id' => $contact->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon"
                                         src="/images/icons/<?=$contact->is_active ? 'action_check.gif':'action_check_off.gif'?>" alt="Активность">
                                </a>
                            <?php else: ?>
                                <a href="<?= Url::toRoute(['contact/activate', 'id' => $contact->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/<?=$contact->is_active ? 'add.gif':'delete.gif'?>" alt="Активность">
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="col-sm-12">
                    <form action="<?=Url::toRoute(['contact/create','clientId'=>$client->id])?>" method="post">
                        <div class="col-sm-2">
                            <select name="type" class="text" style="font-size:10px">
                                <option value="phone">телефон</option>
                                <option value="fax">факс</option>
                                <option value="email">e-mail</option>
                                <option value="sms">СМС</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <input class="text" type="text" name="data" id="i_cd_data">
                        </div>
                        <div class="col-sm-2">
                            <input class="text" type="text" name="comment">
                        </div>
                        <div class="col-sm-2">

                            <?php
                                $arr = [];
                                foreach($contacts as $contact)
                                    if(!in_array($contact->user_id, array_keys($arr)))
                                        $arr[$contact->user_id] = $contact->userName;
                                $arr[Yii::$app->user->id] = Yii::$app->user->identity->name;
                                if($arr){
                                    echo '<select name="user_id">';
                                        foreach($arr as $k=>$v)
                                            echo "<option value=\"$k\">$v</option>";
                                    echo '</select>';
                                }

                            ?>
                        </div>
                        <div class="col-sm-2">
                            <input type="checkbox" class="text" name="is_official" title="официальный?">
                            Официальный
                        </div>
                        <div class="col-sm-2">
                            <input class="button" type="submit" value="добавить">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>