<?php
use \yii\helpers\Url;
use \kartik\widgets\DatePicker;
?>
<?php $docs = $account->contract->allDocuments; ?>

<div class="data-block">
    <div class="row">
        <div class="col-sm-2" onclick="$('.hide-tables').toggle(); return false;" style="cursor: pointer;">
            <a><img class="icon" src="/images/icons/monitoring.gif" alt="Посмотреть"></a>Договор
        </div>
        <div class="col-sm-10">
            <?php foreach ($docs as $doc)
                if ($doc->type == 'contract' && $doc->is_active): ?>
                    <b>
                        <a href="/document/print/&id=<?= $doc->id ?>"
                           target="_blank">
                            <?= $doc->contract_no ?>
                        </a>
                    </b> от <?= $doc->contract_date ?>
                    <span style="font-size:85%">(<?= $doc->user->name ?>, <?= $doc->ts ?>)</span>;
                <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-2" style="cursor: pointer;">
            Бланк заказа
        </div>
        <div class="col-sm-10">
            <?php foreach ($docs as $doc)
                if ($doc->type == 'blank' && $doc->is_active): ?>
                    <b>
                        <a href="/document/print/&id=<?= $doc->id ?>" target="_blank">
                            <?= $doc->contract_no ?>
                        </a>
                    </b> от <?= $doc->contract_date ?>
                    <span style="font-size:85%">(<?= $doc->user->name ?>, <?= $doc->ts ?>)</span>;
                <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-2" style="cursor: pointer;">
            Доп. соглашения
        </div>
        <div class="col-sm-10">
            <?php foreach ($docs as $doc)
                if ($doc->type == 'agreement' && $doc->is_active): ?>
                    <b>
                        <a href="/document/print/&id=<?= $doc->id ?>"
                           target="_blank">
                            <?= $doc->contract_no ?>
                        </a>
                    </b> от <?= $doc->contract_date ?>
                    <span style="font-size:85%">(<?= $doc->user->name ?>, <?= $doc->ts ?>)</span>;
                <?php endif; ?>
        </div>
    </div>

    <div class="row hide-tables" style="display: none;">
        <div class="col-sm-12">
                <div class="row" style="color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
                    <div class="col-sm-12">Договор</div>
                </div>
                <div class="row head3">
                    <div class="col-sm-2">№</div>
                    <div class="col-sm-2">Дата</div>
                    <div class="col-sm-2">Комментарий</div>
                    <div class="col-sm-2">Кто добавил</div>
                    <div class="col-sm-2">Когда</div>
                    <div class="col-sm-2"></div>
                </div>
                <?php foreach ($docs as $doc) if ($doc->type == 'contract'): ?>
                    <div class="row" style="<?= !$doc->is_active ?'color:#CCC;':'' ?>">
                        <div class="col-sm-2"><?= $doc->contract_no ?></div>
                        <div class="col-sm-2"><?= $doc->contract_date ?></div>
                        <div class="col-sm-2"><?= $doc->comment ?></div>
                        <div class="col-sm-2"><?= $doc->user->name ?></div>
                        <div class="col-sm-2"><?= $doc->ts ?></div>
                        <div class="col-sm-2">
                            <a href="/document/edit?id=<?= $doc->id ?>" target="_blank">
                                <img class="icon" src="/images/icons/edit.gif">
                            </a>
                            <a href="/document/print/&id=<?= $doc->id ?>"
                               target="_blank"><img class="icon" src="/images/icons/printer.gif"></a>
                            <a href="/document/send?id=<?= $doc->id ?>" target="_blank">
                                <img class="icon" src="/images/icons/contract.gif">
                            </a>
                            <?php if($doc->is_active) : ?>
                                <a href="<?=Url::toRoute(['document/activate', 'id'=>$doc->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/delete.gif">
                                </a>
                            <?php else : ?>
                                <a href="<?=Url::toRoute(['document/activate', 'id'=>$doc->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/add.gif">
                                </a>
                            <? endif; ?>
                            <a href="https://stat.mcn.ru/view.php?code=<?= $doc->link ?>" target="_blank">ссылка</a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if($account->contract->state == 'unchecked') : ?>
                <div class="row" style="margin-top: 5px;">
                    <form action="/document/create" method="post">
                        <div class="col-sm-2">
                            <input type="hidden" name="ClientDocument[contract_id]" value="<?= $account->contract_id ?>">
                            <input type="hidden" name="ClientDocument[type]" value="contract">
                            <input class="form-control" type="text" name="ClientDocument[contract_no]"
                                   value="<?= $account->contract_id ?>">
                        </div>
                        <div class="col-sm-2">
                            <?= DatePicker::widget(
                                [
                                    'name' => 'ClientDocument[contract_date]',
                                    'value' => date('Y-m-d'),
                                    'removeButton' => false,
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ],
                                ]
                            ); ?>
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="ClientDocument[comment]">
                        </div>

                        <div class="col-sm-2">
                            <select class="form-control tmpl-group" name="ClientDocument[contract_template_group]"
                                    data-type="contract"></select>
                        </div>
                        <div class="col-sm-2">
                            <select class="form-control tmpl" name="ClientDocument[contract_template]" data-type="contract">
                            </select>
                        </div>
                        <div class="col-sm-2">
                                <button type="submit" class="btn btn-default col-sm-12">Зарегистрировать</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
        </div>

        <div class="col-sm-12">
            <div class="row" style="color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
                <div class="col-sm-12">Бланк заказа</div>
            </div>
                <div class="row head3">
                    <div class="col-sm-2">№</div>
                    <div class="col-sm-2">Дата</div>
                    <div class="col-sm-2">Комментарий</div>
                    <div class="col-sm-2">Кто добавил</div>
                    <div class="col-sm-2">Когда</div>
                    <div class="col-sm-2"></div>
                </div>
                <?php foreach ($docs as $doc) if ($doc->type == 'blank'): ?>
                    <?php $blnk = $doc->contract_no; ?>
                    <div class="row" style="<?= !$doc->is_active ?'color:#CCC;':'' ?>">
                        <div class="col-sm-2"><?= $doc->contract_no ?></div>
                        <div class="col-sm-2"><?= $doc->contract_date ?></div>
                        <div class="col-sm-2"><?= $doc->comment ?></div>
                        <div class="col-sm-2"><?= $doc->user->name ?></div>
                        <div class="col-sm-2"><?= $doc->ts ?></div>
                        <div class="col-sm-2">
                            <a href="/document/edit?id=<?= $doc->id ?>"
                               target="_blank"><img
                                    class="icon" src="/images/icons/edit.gif"></a>
                            <a href="/document/print/&id=<?= $doc->id ?>"
                               target="_blank"><img class="icon" src="/images/icons/printer.gif"></a>
                            <a href="/document/send?id=<?= $doc->id ?>"
                               target="_blank"><img class="icon" src="/images/icons/contract.gif"></a>
                            <?php if($doc->is_active) : ?>
                                <a href="<?=Url::toRoute(['document/activate', 'id'=>$doc->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/delete.gif">
                                </a>
                            <?php else : ?>
                                <a href="<?=Url::toRoute(['document/activate', 'id'=>$doc->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/add.gif">
                                </a>
                            <? endif; ?>
                            <a href="https://stat.mcn.ru/view.php?code=<?= $doc->link ?>" target="_blank">ссылка</a>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row" style="margin-top: 5px;">
                    <form action="/document/create" method="post">
                        <div class="col-sm-2">
                            <input type="hidden" name="ClientDocument[contract_id]" value="<?= $account->contract_id ?>">
                            <input type="hidden" name="ClientDocument[type]" value="blank">
                            <input class="form-control" type="text" name="ClientDocument[contract_no]"
                                   value="<?= $blnk ? $doc->contract_no + 1 : 1 ?>"></div>
                        <div class="col-sm-2">
                            <?= DatePicker::widget(
                                [
                                    'name' => 'ClientDocument[contract_date]',
                                    'value' => date('Y-m-d'),
                                    'removeButton' => false,
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ],
                                ]
                            ); ?>
                        </div>
                        <div class="col-sm-2"><input class="form-control" type="text" name="ClientDocument[comment]"></div>
                        <div class="col-sm-2">
                            <select class="form-control tmpl-group" name="ClientDocument[contract_template_group]" data-type="blank"></select>
                        </div>
                        <div class="col-sm-2">
                            <select class="form-control tmpl" name="ClientDocument[contract_template]" data-type="blank"></select>
                        </div>
                        <div class="col-sm-2"><button type="submit" class="btn btn-default col-sm-12">Зарегистрировать</button></div>
                    </form>
                </div>
        </div>

        <div class="col-sm-12">
            <div class="row" style="color: white; background: black; font-weight: bold; margin-top: 10px; text-align: center;">
                <div class="col-sm-12">Доп. соглашения</div>
            </div>
                <div class="row head3">
                    <div class="col-sm-2">№</div>
                    <div class="col-sm-2">Дата</div>
                    <div class="col-sm-2">Комментарий</div>
                    <div class="col-sm-2">Кто добавил</div>
                    <div class="col-sm-2">Когда</div>
                    <div class="col-sm-2"></div>
                </div>
                <?php foreach ($docs as $doc) if ($doc->type == 'agreement'): ?>
                    <?php $armnt = $doc->contract_no; ?>
                    <div class="row" style="<?= !$doc->is_active ?'color:#CCC;':'' ?>">
                        <div class="col-sm-2"><?= $doc->contract_no ?></div>
                        <div class="col-sm-2"><?= $doc->contract_date ?></div>
                        <div class="col-sm-2"><?= $doc->comment ?></div>
                        <div class="col-sm-2"><?= $doc->user->name ?></div>
                        <div class="col-sm-2"><?= $doc->ts ?></div>
                        <div class="col-sm-2">
                            <a href="/document/edit?id=<?= $doc->id ?>"
                               target="_blank"><img
                                    class="icon" src="/images/icons/edit.gif"></a>
                            <a href="/document/print/&id=<?= $doc->id ?>"
                               target="_blank"><img class="icon" src="/images/icons/printer.gif"></a>
                            <a href="/document/send?id=<?= $doc->id ?>"
                               target="_blank"><img class="icon" src="/images/icons/contract.gif"></a>
                            <?php if($doc->is_active) : ?>
                                <a href="<?=Url::toRoute(['document/activate', 'id'=>$doc->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/delete.gif">
                                </a>
                            <?php else : ?>
                                <a href="<?=Url::toRoute(['document/activate', 'id'=>$doc->id])?>">
                                    <img style="margin-left:-2px;margin-top:-3px" class="icon" src="/images/icons/add.gif">
                                </a>
                            <? endif; ?>
                            <a href="https://stat.mcn.ru/view.php?code=<?= $doc->link ?>" target="_blank">ссылка</a>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row" style="margin-top: 5px;">
                    <form action="/document/create" method="post">
                        <div class="col-sm-2">
                            <input type="hidden" name="ClientDocument[contract_id]" value="<?= $account->contract_id ?>">
                            <input type="hidden" name="ClientDocument[type]" value="agreement">
                            <input class="form-control" type="text" name="ClientDocument[contract_no]"
                                   value="<?= $armnt ? $armnt + 1 : 1 ?>"></div>
                        <div class="col-sm-2">
                            <?= DatePicker::widget(
                                [
                                    'name' => 'ClientDocument[contract_date]',
                                    'value' => date('Y-m-d'),
                                    'removeButton' => false,
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'format' => 'yyyy-mm-dd',
                                    ],
                                ]
                            ); ?>
                        </div>
                        <div class="col-sm-2"><input class="form-control" type="text" name="ClientDocument[comment]"></div>
                        <div class="col-sm-2">
                            <select class="form-control tmpl-group" name="ClientDocument[contract_template_group]" data-type="agreement"></select>
                        </div>
                        <div class="col-sm-2">
                            <select class="form-control tmpl" name="ClientDocument[contract_template]" data-type="agreement"></select>
                        </div>
                        <div class="col-sm-2"><button type="submit" class="btn btn-default col-sm-12">Зарегистрировать</button></div>
                    </form>
                </div>
        </div>
    </div>
</div>

<script>
    var folderTranslates = <?= json_encode(\app\dao\ClientDocumentDao::$folders) ?>;
    var folders = <?= json_encode(\app\dao\ClientDocumentDao::templateList(true)) ?>;

    function generateTmplList(type, selected)
    {
        if(!selected)
            selected = $('.tmpl-group[data-type="' + type + '"]').val();
        var tmpl = $('.tmpl[data-type="' + type + '"]');
        if(typeof folders[type] !== 'undefined' && typeof folders[type][selected] !== 'undefined') {
            tmpl.empty();
            $.each(folders[type][selected], function (k, v) {
                tmpl.append('<option value="' + v + '">' + v + '</option>');
            });
        }
    }

    $(function(){
        $('.tmpl-group').each(function(){
            var type = $(this).data('type');
            var t = $(this);
            var first = false;
            $.each(folders[type], function(k,v){
                t.append('<option value="'+ k +'" '+(first?'selected=selected':'')+' >'+ folderTranslates[k] +'</option>');
                if(first == false){
                    first = k;
                }
            });
            generateTmplList(type, first);
        });

        $('.tmpl-group').on('change', function(){
            generateTmplList($(this).data('type'), $(this).val());
        })
    });
</script>