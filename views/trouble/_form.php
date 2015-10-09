<?php
use app\models\Trouble;
use app\widgets\DateControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$timing = [];
if ($curtype) {
    if (in_array($curtype['code'], ['trouble', 'support_welltime']))
        $timing = [
            ['value' => 'time', 'name' => 'Время на устранение', 'input' => Html::textInput('time', 1, ['class' => 'form-control'])],
            ['value' => 'date_finish_desired', 'name' => 'Дата желаемого окончания', 'input' => DateControl::widget(['name' => 'date_finish_desired'])],
        ];
    elseif ($curtype['code'] == 'out')
        $timing = [
            ['value' => 'time', 'name' => 'Время на устранение', 'input' => Html::textInput('time', 1, ['class' => 'form-control'])],
            ['value' => 'date_start', 'name' => 'Дата выезда', 'input' => DateControl::widget(['name' => 'date_start'])],
        ];
    elseif ($curtype['code'] == 'task')
        $timing = [
            ['value' => 'date_start', 'name' => 'Показывать с', 'input' => DateControl::widget(['name' => 'date_start'])],
            ['value' => 'date_finish_desired', 'name' => 'Дата желаемого окончания', 'input' => DateControl::widget(['name' => 'date_finish_desired'])],
        ];

}
?>

<link href="/css/behaviors/media-manager.css" rel="stylesheet"/>

<a href="#" id="toogle-trouble-form"
   style="background: url('/images/icons/add.gif') no-repeat left; padding-left: 17px;"
   data-text="Открыть форму добавления заявки">Скрыть форму добавления заявки</a>

<div class="row" id="create-trouble-form">
    <?= Html::beginForm('?', 'POST', ['enctype' => 'multipart/form-data', 'name' => 'form', 'class' => 'form-vertical']) ?>
    <?= Html::hiddenInput('module', 'tt') ?>
    <?= Html::hiddenInput('action', 'add') ?>
    <div class="col-sm-8">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <a href="/client/view?id=<?= $account->id ?>" style="font-weight: bold;"><?= $account->client ?></a>
                    <?= Html::hiddenInput('client', $account->client) ?>
                </div>
            </div>
        </div>
        <?php if ($ttServer || $ttService) ?>
        <div class="row">
            <?php if ($ttServer): ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <a href="./?module=routers&action=server_pbx_list&id={$tt_server_id}">
                            &nbsp;<?= $ttServer['name'] ?>, Тех.площадка: <?= $ttServer['datacenter_name'] ?>,
                            Регион: <?= $ttServer['datacenter_region'] ?>
                        </a>
                        <?= Html::hiddenInput('server_id', $ttServerId) ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($ttService): ?>
                <div class="col-sm-6">
                    <div class="form-group">
                        <a href="pop_services.php?table=<?= $ttService ?>&id=<?= $ttServiceId ?>"><?= $ttService ?>
                            #<?= $ttServiceId ?></a>
                        <?= Html::hiddenInput('service', $ttServiceId) ?>
                        <?= Html::hiddenInput('service_id', $ttServiceId) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label">Тип</label>
                    <?= Html::dropDownList('', $curtype ? $curtype['code'] : Trouble::TYPE_TROUBLE, $troubleTypes,
                        ['class' => 'form-control', 'disabled' => (bool)$curtype]) ?>

                    <?= Html::hiddenInput('type', $curtype ? $curtype['code'] : Trouble::TYPE_TROUBLE) ?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label class="control-label">Тип заявки</label>
                    <?= Html::dropDownList('trouble_subtype', null, $troubleSubtypes, ['class' => 'form-control']) ?>
                </div>
            </div>
        </div>
        <?php if ($timing): ?>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Ограничение по времени</label>
                        <?= Html::dropDownList('timing', null, ArrayHelper::map($timing, 'value', 'name'), ['class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="col-sm-6 timing-types" style="padding-top: 22px;">
                    <div class="form-group">
                        <?php foreach ($timing as $item)
                            echo '<div id="timing-' . $item['value'] . '">' . $item['input'] . '</div>'; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($billList): ?>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Заказ / Счет</label>
                        <?= Html::dropDownList('bill_no', null, $billList, ['class' => 'form-control']) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-sm-12">
                <div class="form-group">
                    <label>Описание проблемы</label>
                    <?= Html::textarea('problem', null, ['class' => 'form-control']) ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label>Ответственный</label>
                    <?= Html::dropDownList('user', Yii::$app->user->id, $ttUsers, ['class' => 'form-control select2']) ?>
                </div>
            </div>
            <div class="col-sm-6" style="padding-top: 20px;">
                <div class="form-group">
                    <div class="checkbox">
                        <label class="control-label"><?= Html::checkbox('is_important', false) ?>Важная заявка</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4" style="padding-top: 33px;">
        <div class="form-group">
            <label>Прикрепить документы к заявке</label>
            <div class="file_upload form-control input-sm">
                Выбрать файл<input class="media-manager" type="file" name="tt_files[]"/>
            </div>
            <div class="media-manager-block"></div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            <?= Html::submitButton('Завести заявку', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?= Html::endForm() ?>
</div>

<script>
    $('select[name="timing"]').on('change', function () {
        var div = $('#timing-' + $(this).val());
        div.closest('div.form-group').children('div').hide();
        div.show();
    }).trigger('change');

    $('form').on('submit', function () {
        $('.timing-types .form-group div:hidden').remove();
        return true;
    });

    $('#toogle-trouble-form').on('click', function () {
        $('#create-trouble-form').toggle();
        var text = $(this).text();
        $(this).text($(this).data('text')).data('text', text);
        return false;
    });
    <?= !$ttShowAdd ? "$('#toogle-trouble-form').trigger('click');" : '' ?>
</script>

<script type="text/javascript" src="/js/jquery.multifile.min.js"></script>
<script type="text/javascript" src="/js/behaviors/media-manager.js"></script>