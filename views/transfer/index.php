<?php

use app\forms\transfer\ServiceTransferForm;
use kartik\widgets\DatePicker;

/** @var $model ServiceTransferForm */

$possibleServices = $model->getPossibleServices($client, $only_usages);
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

    <div style="overflow: auto; max-height: 500px;">
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
                                Выбранные услуги / устройства
                            </label>
                        </div>

                        <div id="services-list" style="width: 90%; height: auto; visibility: hidden; overflow: auto; margin-left: 20px;">
                            <?php foreach ($possibleServices['items'] as $serviceType => $services): ?>
                                <b><?= $services[0]->helper->title; ?></b>
                                <div class="service-usages">

                                    <?php
                                    /** @var \app\models\Usage[] $services */
                                    foreach ($services as $service):
                                        list($fulltext, $description, $checkboxOptions) = (array) $service->helper->description;

                                        if (mb_strlen($fulltext, 'UTF-8') > 30):
                                            $text = mb_substr($fulltext, 0, 30, 'UTF-8') . '...';
                                        else:
                                            $text = $fulltext;
                                        endif;
                                        ?>

                                        <div class="service-usage">
                                            <?php if (array_key_exists($service->id, $model->servicesErrors)):?>
                                                <img src="/images/icons/error.png" width="16" height="16" border="0" style="vertical-align: top; margin-top: 1px;" title='<?= implode($model->servicesErrors[$service->id], "\n"); ?>' />
                                            <?php endif; ?>

                                            <input
                                                type="checkbox"
                                                name="transfer[source_service_ids][<?php echo get_class($service); ?>][]" value="<?= $service->id; ?>"
                                                checked="checked"
                                                <?= implode(' ', $checkboxOptions); ?> />
                                            &nbsp;<?= $service->id;?>: <abbr title="<?= $service->id . ': ' . $fulltext; ?>"><?= $text; ?></abbr><br />
                                            <?php if (!empty($description)): ?>
                                                <div class="usage-description" style="font-size: 10px; padding-left: 20px;">
                                                    <?= $description; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                    <?= $services[0]->helper->help; ?>
                                    <br />

                                </div>
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
                        $firstRowValue = 'now';

                        foreach ($model->getActualDateVariants() as $date):
                            $date = new DateTime($date);
                            $dateValue = $date->format('Y-m-d');
                            ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="transfer[actual_from]" value="<?= $dateValue; ?>" data-action="date-choose"<?= ($firstRow || $model->actual_from == $dateValue ? 'checked="checked"' : ''); ?> />
                                    <?= $dateValue; ?>
                                </label>
                            </div>
                            <?php
                            if ($firstRow):
                                $firstRowValue = $dateValue;
                            endif;
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
                            'value' => (new DateTime($model->actual_custom ?: $firstRowValue))->format('Y-m-d'),
                            'name' => 'transfer[actual_custom]',
                            'language' => 'ru',
                            'options' => [
                                'style' => 'margin-left: 20px; width: 100px; visibility: hidden;',
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'orientation' => 'top right',
                                'startDate' => 'today',
                            ],
                        ]);
                        ?>

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
                var extend_block = $('input[name*="actual_custom"]');
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

    /** THIS IS DOG-NAIL **/
    $('input[name*="UsageVirtpbx"]')
        .on('change', function() {
            var descr = $(this).parent('.service-usage').find('.usage-description'),
                numbers = descr.text().replace(/[^0-9,]/g, '').split(',');
            for (var i=0,s=numbers.length; i<s; i++) {
                $('input[name*="UsageVoip"]')
                    .next('abbr[title*="' + numbers[i] + '"]')
                    .prev('input').prop('checked', $(this).is(':checked'));
            }
        });

    $('input[name*="UsageVoip"]')
        .on('change', function() {
            var $linkedWith = $('a[data-linked="' + $(this).val() + '"]');
            if ($linkedWith.length) {
                $linkedWith.parents('div.service-usage').find('input[type="checkbox"]').prop('checked', $(this).prop('checked'));
            }
        });
    $('a[data-linked]')
        .on('click', function() {
            $('input[value="' + $(this).data('linked') + '"]').prop('checked', true).trigger('change');
            return false;
        });
    /** THIS IS DOG-NAIL **/

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
