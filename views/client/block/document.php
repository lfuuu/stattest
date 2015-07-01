<?php
use \yii\helpers\Url;
?>
<?php $docs = $client->allDocuments; ?>

<div class="data-block">
    <div class="row">
        <div class="col-sm-2" onclick="$('.hide-tables').toggle(); return false;" style="cursor: pointer;">
            <a><img class="icon" src="/images/icons/monitoring.gif" alt="Посмотреть"></a>Договор
        </div>
        <div class="col-sm-10">
            <?php foreach ($docs as $doc)
                if ($doc->type == 'contract' && $doc->is_active): ?>
                    <b>
                        <a href="index.php?module=clients&id=<?= $doc->id ?>&action=print&data=contract"
                           target="_blank">
                            <?= $doc->contract_no ?>
                        </a>
                    </b> от <?= $doc->contract_date ?>
                    <span style="font-size:85%">(<?= $doc->userName ?>, <?= $doc->ts ?>)</span>;
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
                        <a href="index.php?module=clients&id=<?= $doc->id ?>&action=print&data=contract"
                           target="_blank">
                            <?= $doc->contract_no ?>
                        </a>
                    </b> от <?= $doc->contract_date ?>
                    <span style="font-size:85%">(<?= $doc->userName ?>, <?= $doc->ts ?>)</span>;
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
                        <a href="index.php?module=clients&id=<?= $doc->id ?>&action=print&data=contract"
                           target="_blank">
                            <?= $doc->contract_no ?>
                        </a>
                    </b> от <?= $doc->contract_date ?>
                    <span style="font-size:85%">(<?= $doc->userName ?>, <?= $doc->ts ?>)</span>;
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
                        <div class="col-sm-2"><?= $doc->userName ?></div>
                        <div class="col-sm-2"><?= $doc->ts ?></div>
                        <div class="col-sm-2">
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=contract_edit"
                               target="_blank"><img
                                    class="icon" src="/images/icons/edit.gif"></a>
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=print&data=contract"
                               target="_blank"><img class="icon" src="/images/icons/printer.gif"></a>
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=send&data=contract"
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
                    <form action="/document/create?id=<?= $client->id ?>" method="post">
                        <div class="col-sm-2">
                            <input type="hidden" name="contract_type" value="contract">
                            <input class="form-control" type="text" name="contract_no"
                                   value="<?= $client->contract->id ?>">
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control contract_datepicker" type="text"
                                   name="contract_date">
                        </div>
                        <div class="col-sm-2">
                            <input class="form-control" type="text" name="comment">
                        </div>

                        <div class="col-sm-2">
                            <select class="form-control" name="contract_template_group"
                                    id="contract_template_group_contract"
                                    onchange="do_change_template_group(this, 'contract')">
                                <option value="MCN">MCN</option>
                                <option value="Межоператорка">Межоператорка</option>
                                <option value="Партнеры">Партнеры</option>
                                <option value="Интернет-магазин">Интернет-магазин</option>
                                <option value="WellTime">WellTime</option>
                                <option value="IT-Park">IT-Park</option>
                                <option value="Arhiv">Arhiv</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select class="form-control" name="contract_template"
                                    id="contract_template_contract">
                                <option value="Dog_UslugiSvayzi">Dog_UslugiSvayzi</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-default col-sm-12">Загрузить</button>
                        </div>
                    </form>
                </div>
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
                        <div class="col-sm-2"><?= $doc->userName ?></div>
                        <div class="col-sm-2"><?= $doc->ts ?></div>
                        <div class="col-sm-2">
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=contract_edit"
                               target="_blank"><img
                                    class="icon" src="/images/icons/edit.gif"></a>
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=print&data=contract"
                               target="_blank"><img class="icon" src="/images/icons/printer.gif"></a>
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=send&data=contract"
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
                    <form action="/document/create?id=<?= $client->id ?>" method="post">
                        <div class="col-sm-2"><input type="hidden" name="contract_type" value="blank">
                            <input class="form-control" type="text" name="contract_no"
                                   value="<?= $blnk ? $doc->contract_no + 1 : 1 ?>"></div>
                        <div class="col-sm-2"><input class="form-control contract_datepicker" type="text" name="contract_date">
                        </div>
                        <div class="col-sm-2"><input class="form-control" type="text" name="comment"></div>
                        <div class="col-sm-2">
                            <select class="form-control" name="contract_template_group" id="contract_template_group_contract"
                                    onchange="do_change_template_group(this, 'contract')">
                                <option value="MCN">MCN</option>
                                <option value="Межоператорка">Межоператорка</option>
                                <option value="Партнеры">Партнеры</option>
                                <option value="Интернет-магазин">Интернет-магазин</option>
                                <option value="WellTime">WellTime</option>
                                <option value="IT-Park">IT-Park</option>
                                <option value="Arhiv">Arhiv</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select class="form-control" name="contract_template" id="contract_template_contract">
                                <option value="Dog_UslugiSvayzi">Dog_UslugiSvayzi</option>
                            </select>
                        </div>
                        <div class="col-sm-2"><button type="submit" class="btn btn-default col-sm-12">Загрузить</button></div>
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
                        <div class="col-sm-2"><?= $doc->userName ?></div>
                        <div class="col-sm-2"><?= $doc->ts ?></div>
                        <div class="col-sm-2">
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=contract_edit"
                               target="_blank"><img
                                    class="icon" src="/images/icons/edit.gif"></a>
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=print&data=contract"
                               target="_blank"><img class="icon" src="/images/icons/printer.gif"></a>
                            <a href="index.php?module=clients&id=<?= $doc->id ?>&action=send&data=contract"
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
                    <form action="/document/create?id=<?= $client->id ?>" method="post">
                        <div class="col-sm-2"><input type="hidden" name="contract_type" value="agreement">
                            <input class="form-control" type="text" name="contract_no"
                                   value="<?= $armnt ? $armnt + 1 : 1 ?>"></div>
                        <div class="col-sm-2"><input class="form-control contract_datepicker" type="text" name="contract_date">
                        </div>
                        <div class="col-sm-2"><input class="form-control" type="text" name="comment"></div>
                        <div class="col-sm-2">
                            <select class="form-control" name="contract_template_group" id="contract_template_group_agreement"
                                    onchange="do_change_template_group(this, 'agreement')">
                                <option value="MCN Телефония">MCN Телефония</option>
                                <option value="MCN Интернет">MCN Интернет</option>
                                <option value="MCN Дата-центр">MCN Дата-центр</option>
                                <option value="MCN-СПб">MCN-СПб</option>
                                <option value="MCN-Краснодар">MCN-Краснодар</option>
                                <option value="MCN-Самара">MCN-Самара</option>
                                <option value="MCN-Екатеринбург">MCN-Екатеринбург</option>
                                <option value="MCN-Новосибирск">MCN-Новосибирск</option>
                                <option value="MCN-Ростов-на-Дону">MCN-Ростов-на-Дону</option>
                                <option value="MCN-НижнийНовгород">MCN-НижнийНовгород</option>
                                <option value="MCN-Казань">MCN-Казань</option>
                                <option value="MCN-Владивосток">MCN-Владивосток</option>
                                <option value="Arhiv">Arhiv</option>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <select class="form-control" name="contract_template" id="contract_template_agreement">
                                <option value="DC_100kanalnynomer">DC_100kanalnynomer</option>
                                <option value="DC_Special100kanalny">DC_Special100kanalny</option>
                                <option value="DC_nomervpodarok_bronza">DC_nomervpodarok_bronza</option>
                                <option value="DC_telefonia">DC_telefonia</option>
                                <option value="DS_Nomervpodarok">DS_Nomervpodarok</option>
                                <option value="DS_Test">DS_Test</option>
                                <option value="Dop_8800">Dop_8800</option>
                            </select>
                        </div>
                        <div class="col-sm-2"><button type="submit" class="btn btn-default col-sm-12">Загрузить</button></div>
                    </form>
                </div>
        </div>
    </div>
</div>
