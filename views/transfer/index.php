<?php

use app\forms\transfer\ServiceTransferForm;
use kartik\widgets\DatePicker;

/** @var $model ServiceTransferForm */

$possibleServices = $model->getPossibleServices($client);
?>

<form method="POST" action="/transfer/index/?client=<?php echo $client->id; ?>">
    <table border="0" width="95%" align="center">
        <col width="40%" />
        <col width="40%" />
        <col width="15%" />

        <thead>
            <tr>
                <th colspan="3">
                    <h2>Лицевой счет № <?php echo $client->id; ?> <?php echo $client->firma; ?></h2>
                    <hr size="1" />
                </th>
            </tr>
            <tr>
                <th>Перенести</th>
                <th>на лицевой счет</th>
                <th>дата переноса</th>
            </tr>
        </thead>
    </table>

    <div style="overflow: auto; max-height: 400px;">
        <table border="0" width="95%" align="center">
            <col width="40%" />
            <col width="40%" />
            <col width="15%" />

            <thead>
                <tr>
                    <th valign="top">
                        <?php if ($model->getFirstError('source_service_ids') || $model->getFirstError('services_got_errors')): ?>
                            <div class="label label-danger">
                                <?= $model->getFirstError('source_service_ids'); ?>
                                <?= $model->getFirstError('services_got_errors'); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (sizeof($model->servicesSuccess)): ?>
                            <br />
                            <div class="label label-success">
                                Успешно перенесено <?= sizeof($model->servicesSuccess); ?>
                            </div>
                        <?php endif; ?>
                    </th>
                    <th valign="top">
                        <?php if ($model->getFirstError('target_account_id') || $model->getFirstError('target_account_custom')): ?>
                            <div class="label label-danger">
                                <?= $model->getFirstError('target_account_id'); ?>
                                <?= $model->getFirstError('target_account_custom'); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($model->getFirstError('target_account_not_found')): ?>
                            <div class="label label-danger">
                                <?= $model->getFirstError('target_account_not_found'); ?>
                            </div>
                        <?php endif; ?>
                    </th>
                    <th valign="top">
                        <?php if ($model->getFirstError('actual_from') || $model->getFirstError('actual_custom')): ?>
                            <div class="label label-danger">
                                <?= $model->getFirstError('actual_from'); ?>
                                <?= $model->getFirstError('actual_custom'); ?>
                            </div>
                        <?php endif; ?>
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td valign="top">
                        <div class="radio">
                            <label>
                                <input type="radio" name="services-choose" value="all" data-action="services-choose"<?= (!sizeof($model->servicesErrors) ? 'checked="checked"' : ''); ?> />
                                Все (<?= $possibleServices['total'];?> шт.)
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="services-choose" value="custom" data-action="services-choose"<?= (sizeof($model->servicesErrors) ? ' checked="checked"' : ''); ?> />
                                Выбранные услуги
                            </label>
                        </div>

                        <div id="services-list" style="width: 90%; height: auto; visibility: hidden; overflow: auto; margin-left: 20px;">
                            <?php foreach ($possibleServices['items'] as $serviceType => $services): ?>
                                <b><?= $serviceType::getTypeTitle(); ?></b><br />

                                <?php
                                /** @var \app\models\Usage[] $services */
                                foreach ($services as $service):
                                    $fulltext = $service->getTypeDescription();

                                    if (mb_strlen($fulltext, 'UTF-8') > 30):
                                        $text = mb_substr($fulltext, 0, 30, 'UTF-8') . '...';
                                    else:
                                        $text = $fulltext;
                                    endif;
                                    ?>

                                    <?php if (array_key_exists($service->id, $model->servicesErrors)):?>
                                        <img src="/images/icons/error.png" width="16" height="16" border="0" style="vertical-align: top; margin-top: 1px;" title='<?= implode($model->servicesErrors[$service->id], "\n"); ?>' />
                                    <?php endif; ?>

                                    <input type="checkbox" name="transfer[source_service_ids][<?php echo get_class($service); ?>][]" value="<?= $service->id; ?>" checked="checked" />
                                    &nbsp;<?= $service->id;?>: <abbr title="<?= $service->id . ': ' . $fulltext; ?>"><?= $text; ?></abbr><br />
                                <?php endforeach; ?>
                                <br />
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td valign="top">
                        <?php
                        $firstRow = (boolean) !(int) $model->target_account_id;

                        if (!is_null($model->targetAccount)):
                            $firstRow = false;
                            ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="transfer[target_account_id]" value="<?= $model->targetAccount->id; ?>" data-action="account-choose" checked="checked" />
                                    № <?= $model->targetAccount->id; ?> - <?= $model->targetAccount->contragent->name; ?>
                                </label>
                            </div>
                        <?php
                        endif;

                        foreach ($model->getClientAccounts($client) as $account):
                            if (!is_null($model->targetAccount) && $account->id == $model->targetAccount->id)
                                continue;
                            ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="transfer[target_account_id]" value="<?= $account->id; ?>" data-action="account-choose"<?= ($firstRow || $model->target_account_id == $account->id ? 'checked="checked"' : ''); ?> />
                                    № <?= $account->id; ?> - <?= $account->contragent->name; ?>
                                </label>
                            </div>
                            <?php
                            $firstRow = false;
                        endforeach;
                        ?>

                        <div class="radio" data-custom="1">
                            <label>
                                <input type="radio" name="transfer[target_account_id]" value="custom" data-action="account-choose" />
                                Другой клиент
                            </label>
                        </div>

                        <input type="text" name="target_account_search" class="form-control" style="margin-left: 20px; width: 70%; visibility: hidden;" />
                        <input type="hidden" name="transfer[target_account_id_custom]" value="0" />
                    </td>
                    <td valign="top">
                        <?php
                        $firstRow = (boolean) !$model->actual_from;

                        foreach ($model->getActualDateVariants() as $date):
                            $date = new DateTime($date);
                            $dateValue = $date->format('Y-m-d');
                            ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="transfer[actual_from]" value="<?= $dateValue; ?>" data-action="date-choose"<?= ($firstRow || $model->actual_from == $dateValue ? 'checked="checked"' : ''); ?> />
                                    <?= $date->format('d.m.Y'); ?>
                                </label>
                            </div>
                            <?php
                            $firstRow = false;
                        endforeach;
                        ?>
                        <div class="radio">
                            <label>
                                <input type="radio" name="transfer[actual_from]" value="custom" data-action="date-choose"<?= ($model->actual_from == 'custom' ? 'checked="checked"' : ''); ?> />
                                Другая дата
                            </label>
                        </div>
                        <?php
                        echo DatePicker::widget([
                            'type' => DatePicker::TYPE_INPUT,
                            'value' => (new DateTime($model->actual_custom?:"now"))->format("d.m.Y"),
                            'name' => 'actual_from_datepicker',
                            'language' => 'ru',
                            'options' => [
                                'style' => 'margin-left: 20px; width: 100px'
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'dd.mm.yyyy',
                                'orientation' => 'top right',
                                'startDate' =>  'today'
                            ],
                            'pluginEvents' => [
                                'changeDate' => "function(e) {
                                    $('input[name=\"transfer[actual_custom]\"]').val(e.format(0, 'yyyy-mm-dd'));
                                }"
                            ]
                        ]);
                        ?>
                        <input type="hidden" name="transfer[actual_custom]" value="<?=$model->actual_custom?>" />

                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="position: fixed; bottom: 0; right: 15px;">
        <button type="button" id="dialog-close" style="width: 100px; margin-right: 15px;" class="btn btn-link">Отмена</button>
        <button type="submit" style="width: 100px;" class="btn btn-primary"<?= (!$possibleServices['total'] ? 'disabled="disabled"' : '');?>>OK</button>
    </div>
</form>

<script type="text/javascript">
/**
* Для переноса услуг, создание и обработка popup
*/
jQuery(document).ready(function() {
    var $actions = {
            'services-choose': function(element) {
                var extend_block = $('#services-list');
                if (element.val() == 'custom') {
                    extend_block.find('input[type="checkbox"]').removeAttr('checked').prop('checked', false);
                    extend_block.css('visibility', 'visible');
                }
                else {
                    extend_block.find('input[type="checkbox"]').attr('checked', 'checked').prop('checked', true);
                    extend_block.css('visibility', 'hidden');
                }
            },
            'date-choose': function(element) {
                var extend_block = $('input[name="actual_from_datepicker"]');
                element.val() == 'custom' ? extend_block.css('visibility', 'visible') : extend_block.css('visibility', 'hidden');
            },
            'account-choose': function(element) {
                var extend_block = $('input[name="target_account_search"]');
                element.val() == 'custom' ? extend_block.css('visibility', 'visible') : extend_block.css('visibility', 'hidden');
            }
        };

    $('#dialog-close').click(function() {
        window.parent.$dialog.dialog('close');
    });

    $(document).bind('keydown', function(e) {
        if (e.keyCode === $.ui.keyCode.ESCAPE)
            $('#dialog-close').trigger('click');
    });

    $('input[name="target_account_search"]')
        .bind('keydown', function(e) {
            if (e.keyCode === $.ui.keyCode.TAB && $(this).autocomplete('instance').menu.active)
                e.preventDefault();
            if (e.keyCode === $.ui.keyCode.ENTER)
                $(this).blur();
        })
        .bind('blur', function() {
            if ($(this).val().test(/^[0-9]+$/))
                $('input[name="transfer[target_account_id_custom]"]').val($(this).val());
        })
        .autocomplete({
            source: '/transfer/account-search?client_id=<?php echo $client->id;?>',
            minLength: 2,
            focus: function() {
                return false;
            },
            select: function(event, ui) {
                $('input[name="transfer[target_account_id_custom]"]').val(ui.item.value);
                $(this).val(ui.item.label);
                return false;
            }
        })
        .data('autocomplete')._renderItem = function(ul, item) {
            return $('<li />')
                .data('item.autocomplete', item)
                .append('<a title="' + item.full + '">' + item.label + '</a>')
                .appendTo(ul);
        };

    $(document).on('change', 'input[data-action]', function() {
        if ($(this).has(':checked') && $.isFunction($actions[$(this).data('action')]))
            $actions[$(this).data('action')]($(this));
    });

    $('input[name="services-choose"]:checked').trigger('change');
    $('input[name="transfer[actual_from]"]:checked').trigger('change');
});
</script>

<style type="text/css">
.ui-autocomplete-loading {
    background: white url('images/ajax-loader-small.gif') right center no-repeat;
}
.ui-autocomplete {
    max-height: 145px;
    overflow-y: auto;
    overflow-x: hidden;
}
.ui-menu-item {
    white-space: nowrap;
}
.form-control {
    padding: 0;
    height: auto;
    font-size: 12px;
}
</style>
