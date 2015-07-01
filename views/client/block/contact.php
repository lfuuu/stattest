<?php
use \yii\helpers\Url;
?>
<?php $contacts = $client->allContacts; ?>

<div class="data-block row data-contacts">
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
                    <div class="col-sm-2">Метки</div>
                    <div class="col-sm-1">Тип</div>
                    <div class="col-sm-2">Контакт</div>
                    <div class="col-sm-2">Комментарий</div>
                    <div class="col-sm-2">Кто добавил</div>
                    <div class="col-sm-2">Когда</div>
                    <div class="col-sm-1"></div>
                </div>
                <?php foreach ($contacts as $contact) : ?>
                    <div class="col-sm-12">
                        <div class="col-sm-2">
                            <?php $tagsForAdd = \app\models\Tag::getListByGroupId(1); ?>

                            <?php foreach($contact->getTags() as $tag) :  ?>
                                <?php unset($tagsForAdd[$tag->tag_id]) ?>
                                <span title="<?=$tag->user->name?>(<?=$tag->create_at?>)" >
                                    <?=$tag->tag->name?>
                                    <a class="btn-del-tag"
                                       data-model-name="<?=$tag->model?>"
                                       data-model-id="<?=$tag->model_id?>"
                                       data-tag-id="<?=$tag->tag_id?>"
                                        >
                                        <i class="uncheck" style="font-size: 12px;"></i>
                                    </a>
                                </span>
                            <?php endforeach; ?>
                            <i class="glyphicon glyphicon-plus btn-add-tag" style="color: green; font-size: 12px;"></i>
                            <div class="box-add-tag"
                                style="position: absolute; top: 100%; left:0; right: 0; display: none; text-align: center; padding: 10px; background: white; z-index: 1;">
                                <select style="font-size:10px">
                                    <?php
                                    foreach($tagsForAdd as $k=>$v) :
                                    ?>
                                    <option value="<?=$k?>"><?=$v?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn-save-tag" style="font-size: 12px;"
                                        data-model-name="<?=\app\models\TagToModel::getFormattedClassName($contact::className())?>"
                                        data-model-id="<?=$contact->id?>"
                                    >
                                    Сохранить
                                </button>
                            </div>
                        </div>
                        <div class="col-sm-1"><?= $contact->type ?></div>
                        <div class="col-sm-2"><?= $contact->data ?></div>
                        <div class="col-sm-2"><?= $contact->comment ?></div>
                        <div class="col-sm-2"><?= $contact->userName ?></div>
                        <div class="col-sm-2"><?= $contact->ts ?></div>
                        <div class="col-sm-1">
                            <?php if ($contact->userUser == 'AutoLK'): ?>
                                <a href="<?= Url::toRoute(['contact/lk-activate', 'id' => $contact->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon"
                                         src="/images/icons/<?=$contact->is_active ? 'action_check_off.gif':'action_check.gif'?>" alt="Активность">
                                </a>
                            <?php else: ?>
                                <a href="<?= Url::toRoute(['contact/activate', 'id' => $contact->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/<?=$contact->is_active ? 'delete.gif':'add.gif'?>" alt="Активность">
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

<script>
    $(function(){
        $('.data-contacts').on('click', '.btn-del-tag', function(e){
            e.preventDefault();
            var t = $(this);
            var params = {
                modelName: t.data('model-name'),
                tagId: t.data('tag-id'),
                modelId: t.data('model-id')
            }
            var optionHtml = '<option value="' + params.tagId + '">'+ t.parent().text() +'</option>';
            console.log(optionHtml);
            console.log(t.parent().parent().find('.box-add-tag select'));
            t.parent().parent().find('.box-add-tag select').append(optionHtml);
            t.parent().remove();
            $.get('/tag/unset', params);
        });

        $('.data-contacts').on('click', '.btn-add-tag', function(e){
            $('.box-add-tag').hide();
            $(this).next().show();
        });

        $('.data-contacts').on('click', '.btn-save-tag', function(e){
            var t = $(this);
            var params = {
                modelName: t.data('model-name'),
                tagId: t.prev().val(),
                modelId: t.data('model-id')
            };
            var tagName = t.prev().find('option:selected').text();
            var tagHtml = '<span>' + tagName +
                '<a href="/tag/unset" class="btn-del-tag" ' +
                    'data-tag-id="' + params.tagId +'" ' +
                    'data-model-id="' + params.modelId + '" ' +
                    'data-model-name="' + params.modelName + '">' +
                ' <i class="uncheck" style="font-size: 12px;"></i>' +
                '</a></span>';
            t.parent().parent().prepend(tagHtml);
            t.prev().find('option:selected').remove();
            $.get('/tag/set', params);
            t.parent().hide();
        });
    });
</script>